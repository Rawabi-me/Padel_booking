export default function TimeSlotGrid({ slots, selectedTimes, onToggle, loading }) {
  if (loading) {
    return <p className="muted">جارِ تحميل الأوقات المتاحة...</p>;
  }

  if (!slots || slots.length === 0) {
    return <p className="muted">لا توجد أوقات متاحة في هذا اليوم.</p>;
  }

  return (
    <div className="slot-grid">
      {slots.map((slot) => {
        const isSelected = selectedTimes.includes(slot.start_time);
        return (
          <button
            key={slot.start_time}
            type="button"
            className={`slot-btn ${isSelected ? "selected" : ""}`}
            onClick={() => onToggle(slot)}
          >
            {slot.start_time} - {slot.end_time}
          </button>
        );
      })}
    </div>
  );
}
