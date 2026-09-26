import FulfillmentToggle from '@/components/checkout/fulfillment-toggle';
import SlotPicker from '@/components/checkout/slot-picker';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { FulfillmentPageProps, TimeSlotOption, FeeQuote } from '@/types';
import {fulfillment, quote} from "@/routes/checkout";
import {store as storeFulfillment} from "@/routes/checkout/fulfillment";

export default function FulfillmentStep({ store, type, zip, zipError, slotsByDate, subtotal, fees }: FulfillmentPageProps) {
    const [selectedSlot, setSelectedSlot] = useState<TimeSlotOption | null>(null);
    const [address, setAddress] = useState({ address: '', city: '', state: 'CO', zip: zip ?? '' });
    const [vehicle, setVehicle] = useState('');
    const [tip, setTip] = useState<number>(0);
    const [liveFees, setLiveFees] = useState<FeeQuote>(fees);
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const formatPrice = (n: number) =>
        new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);

    // Re-quote fees when mode or tip changes
    const updateTip = (newTip: number) => {
        setTip(newTip);
        router.post(quote(),
            { type, subtotal, tip: newTip },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => setLiveFees(page.props.quote as FeeQuote),
            }
        );
    };

    // Re-validate zip when typed (debounced in production)
    const validateZip = (newZip: string) => {
        setAddress(prev => ({ ...prev, zip: newZip }));
        if (newZip.length === 5) {
            router.get(fulfillment(), { type, zip: newZip }, { preserveState: true });
        }
    };

    const submit = () => {
        if (!selectedSlot) {
            setErrors({ slot: 'Please select a time slot.' });
            return;
        }
        setProcessing(true);

        router.post(storeFulfillment(), {
            type,
            time_slot_id: selectedSlot.id,
            slot_date: selectedSlot.date,
            ...(type === 'delivery' ? { ...address } : { vehicle_description: vehicle }),
            tip,
        }, {
            onFinish: () => setProcessing(false),
            onError: (errs) => setErrors(errs as Record<string, string>),
        });
    };

    return (
        <>
            <Head title="Checkout" />
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

                    {/* Step indicator */}
                    <ol className="flex items-center gap-2 text-sm">
                        {['Fulfillment', 'Review', 'Payment'].map((step, i) => (
                            <li key={step} className={`flex items-center gap-2 ${i === 0 ? 'text-purple-600 font-medium' : 'text-gray-400'}`}>
                                {i > 0 && <span className="text-gray-300">→</span>}
                                {step}
                            </li>
                        ))}
                    </ol>

                    {/* Store context */}
                    <div className="bg-blue-50 border border-blue-100 rounded-lg p-4 text-sm text-blue-800">
                        Ordering from <strong>{store.name}</strong> — {store.address}, {store.city}, {store.state}
                    </div>

                    {/* Mode toggle */}
                    <section>
                        <h3 className="font-semibold text-gray-900 mb-3">How would you like to get your groceries?</h3>
                        <FulfillmentToggle type={type} zip={address.zip || undefined} />
                    </section>

                    {/* Mode-specific details */}
                    <section>
                        <h3 className="font-semibold text-gray-900 mb-3">
                            {type === 'delivery' ? 'Delivery Address' : 'Curbside Details (optional)'}
                        </h3>

                        {type === 'delivery' ? (
                            <div className="space-y-3">
                                {zipError && (
                                    <div className="bg-red-50 border border-red-200 text-red-700 rounded-md p-3 text-sm">{zipError}</div>
                                )}
                                <input
                                    value={address.address}
                                    onChange={e => setAddress(p => ({ ...p, address: e.target.value }))}
                                    placeholder="Street address"
                                    className="w-full border-gray-300 rounded-md border p-2 text-sm"
                                />
                                <div className="grid grid-cols-3 gap-3">
                                    <input
                                        value={address.city}
                                        onChange={e => setAddress(p => ({ ...p, city: e.target.value }))}
                                        placeholder="City"
                                        className="border-gray-300 rounded-md border p-2 text-sm"
                                    />
                                    <input
                                        value={address.state}
                                        onChange={e => setAddress(p => ({ ...p, state: e.target.value.toUpperCase().slice(0, 2) }))}
                                        placeholder="State"
                                        className="border-gray-300 rounded-md border p-2 text-sm"
                                    />
                                    <input
                                        value={address.zip}
                                        onChange={e => validateZip(e.target.value)}
                                        placeholder="ZIP"
                                        className="border-gray-300 rounded-md border p-2 text-sm"
                                    />
                                </div>
                            </div>
                        ) : (
                            <input
                                value={vehicle}
                                onChange={e => setVehicle(e.target.value)}
                                placeholder="e.g., Blue Honda CR-V (helps staff find you)"
                                className="w-full border-gray-300 rounded-md border p-2 text-sm"
                            />
                        )}
                    </section>

                    {/* Slot picker */}
                    <section>
                        <h3 className="font-semibold text-gray-900 mb-3">Choose a {type === 'delivery' ? 'Delivery Window' : 'Pickup Time'}</h3>
                        <SlotPicker
                            slotsByDate={slotsByDate}
                            selectedSlot={selectedSlot}
                            onSelect={(slot) => { setSelectedSlot(slot); setErrors({}); }}
                        />
                        {errors.slot && <p className="text-sm text-red-600 mt-2">{errors.slot}</p>}
                    </section>

                    {/* Tip (delivery only) */}
                    {type === 'delivery' && (
                        <section>
                            <h3 className="font-semibold text-gray-900 mb-3">Driver Tip</h3>
                            <div className="flex gap-2">
                                {[0, 5, 10, 15].map(amount => (
                                    <button
                                        key={amount}
                                        onClick={() => updateTip(amount)}
                                        className={`px-4 py-2 rounded-lg border text-sm font-medium ${
                                            tip === amount ? 'border-purple-600 bg-purple-600 text-white' : 'border-gray-200 hover:border-gray-300'
                                        }`}
                                    >
                                        {amount === 0 ? 'None' : formatPrice(amount)}
                                    </button>
                                ))}
                            </div>
                        </section>
                    )}

                    {/* Sticky summary + submit */}
                    <div className="bg-gray-50 rounded-lg p-6 border border-gray-200 space-y-2 sticky bottom-4">
                        <div className="flex justify-between text-sm"><span>Subtotal</span><span>{formatPrice(subtotal)}</span></div>
                        <div className="flex justify-between text-sm">
                            <span>{type === 'delivery' ? 'Delivery' : 'Pickup'}</span>
                            <span>{liveFees.fee === 0 ? 'FREE' : formatPrice(liveFees.fee)}</span>
                        </div>
                        {type === 'delivery' && tip > 0 && (
                            <div className="flex justify-between text-sm"><span>Tip</span><span>{formatPrice(tip)}</span></div>
                        )}
                        {type === 'delivery' && liveFees.fee > 0 && (
                            <div className="text-xs text-gray-500">
                                Add {formatPrice(liveFees.free_delivery_threshold - subtotal)} more for free delivery
                            </div>
                        )}
                        <div className="flex justify-between font-semibold text-gray-900 pt-2 border-t border-gray-200">
                            <span>Total before tax</span>
                            <span>{formatPrice(subtotal + liveFees.fee + liveFees.tip)}</span>
                        </div>

                        <button
                            onClick={submit}
                            disabled={processing || !selectedSlot || (!!zipError && type === 'delivery')}
                            className="w-full mt-4 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white font-medium py-3 rounded-md transition-colors"
                        >
                            {processing ? 'Reserving your slot…' : 'Continue to Review'}
                        </button>
                    </div>
                </div>
            </div>
        </>
    );
}

FulfillmentStep.layout = {
    breadcrumbs: [
        {
            title: 'Checkout — Fulfillment',
            href: fulfillment()
        }
    ]
}
