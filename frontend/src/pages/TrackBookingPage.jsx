import { useState } from "react";
import { getBooking } from "../api/client";

export default function TrackBookingPage() {
  const [reference, setReference] = useState("");
  const [booking, setBooking] = useState(null);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSearch(e) {
    e.preventDefault();
    setError("");
    setBooking(null);
    setLoading(true);
    try {
      const data = await getBooking(reference.trim());
      setBooking(data);
    } catch {
      setError("لم يتم العثور على حجز بهذا الرقم المرجعي.");
    } finally {
      setLoading(false);
    }
  }

  const statusLabel = { paid: "مدفوع", pending: "قيد الانتظار", failed: "فشل" };

  return (
    <div className="track-page" style={{ maxWidth: 480, margin: "2rem auto" }}>
      <h2>تتبع حجزك</h2>
      <form onSubmit={handleSearch}>
        <input
          type="text"
          placeholder="رقم الحجز المرجعي (مثال: PB-XXXXXXXX)"
          value={reference}
          onChange={(e) => setReference(e.target.value)}
          required
        />
        <button type="submit" disabled={loading}>
          {loading ? "جارِ البحث..." : "بحث"}
        </button>
      </form>

      {error && <p className="error-msg">{error}</p>}

      {booking && (
        <div className="cart-panel" style={{ marginTop: "1rem" }}>
          <p><strong>الرقم المرجعي:</strong> {booking.booking_reference}</p>
          <p><strong>الحالة:</strong> {statusLabel[booking.payment_status]}</p>
          <p><strong>الإجمالي:</strong> {Number(booking.total_amount).toFixed(3)} ر.ع</p>
          <ul>
            {booking.slots.map((s) => (
              <li key={s.date + s.start_time}>
                {s.date} — {s.start_time} إلى {s.end_time}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}