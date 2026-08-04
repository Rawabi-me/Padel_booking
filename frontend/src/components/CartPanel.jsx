import { pricePerHourFor } from "../utils/pricing";

export default function CartPanel({ cart, tiers, onRemove }) {
  const dates = [...new Set(cart.map((c) => c.date))].sort();

  let total = 0;

  return (
    <div className="cart-panel">
      <h3>سلة الحجز</h3>
      {dates.length === 0 && <p className="muted">لم تختر أي وقت بعد.</p>}

      {dates.map((date) => {
        const items = cart.filter((c) => c.date === date);
        const pricePerHour = pricePerHourFor(tiers, items.length);
        const subtotal = pricePerHour * items.length;
        total += subtotal;

        return (
          <div key={date} className="cart-day">
            <div className="cart-day-header">
              <strong>{date}</strong>
              <span className="muted">{items.length} ساعة × {pricePerHour.toFixed(3)} ر.ع</span>
            </div>
            <ul>
              {items.map((item) => (
                <li key={item.date + item.start_time}>
                  {item.start_time} - {item.end_time}
                  <button type="button" className="link-btn" onClick={() => onRemove(item)}>إزالة</button>
                </li>
              ))}
            </ul>
            <div className="cart-day-subtotal">الإجمالي الجزئي: {subtotal.toFixed(3)} ر.ع</div>
          </div>
        );
      })}

      {dates.length > 0 && (
        <div className="cart-total">الإجمالي الكلي: <strong>{total.toFixed(3)} ر.ع</strong></div>
      )}
    </div>
  );
}
