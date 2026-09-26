import { useState } from 'react';
import { SlotDayGroup, TimeSlotOption } from '@/types';

interface SlotPickerProps {
    slotsByDate: SlotDayGroup[];
    selectedSlot: TimeSlotOption | null;
    onSelect: (slot: TimeSlotOption) => void;
}

export default function SlotPicker({ slotsByDate, selectedSlot, onSelect }: SlotPickerProps) {
    const [activeDate, setActiveDate] = useState(slotsByDate[0]?.date ?? null);
    const activeDay = slotsByDate.find(d => d.date === activeDate);

    if (slotsByDate.length === 0) {
        return (
            <div className="text-center py-8 bg-gray-50 rounded-lg text-gray-500">
                No time slots available. Please try another fulfillment option or check back soon.
            </div>
        );
    }

    return (
        <div>
            {/* Date selector */}
        <div className="flex gap-2 overflow-x-auto pb-2 mb-4">
        {slotsByDate.map(day => (
                <button
                    key={day.date}
            onClick={() => setActiveDate(day.date)}
    className={`flex-shrink-0 px-4 py-2 rounded-lg border text-sm font-medium ${
        activeDate === day.date
            ? 'border-purple-600 bg-purple-600 text-white'
            : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
    }`}
>
    {day.label}
    </button>
))}
    </div>

    {/* Slots for active date */}
    <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
        {activeDay?.slots.map(slot => {
            const isSelected = selectedSlot?.id === slot.id && selectedSlot?.date === slot.date;
            return (
                <button
                    key={`${slot.date}-${slot.id}`}
            onClick={() => onSelect(slot)}
            disabled={slot.remaining === 0}
            className={`p-3 rounded-lg border text-sm transition-colors ${
                isSelected
                    ? 'border-purple-600 bg-purple-600 text-white'
                    : 'border-gray-200 bg-white hover:border-gray-300 disabled:opacity-40'
            }`}
        >
            <div className="font-medium">{slot.label}</div>
            {!isSelected && slot.almost_full && (
                <div className={`text-xs mt-1 ${slot.remaining <= 2 ? 'text-orange-500' : ''}`}>
                Only {slot.remaining} left
            </div>
            )}
            </button>
        );
        })}
    </div>
    </div>
);
}
