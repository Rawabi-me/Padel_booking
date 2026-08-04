import { useEffect, useState } from "react";
import TimeSlotGrid from "../components/TimeSlotGrid";
import CartPanel from "../components/CartPanel";
import CustomerForm from "../components/CustomerForm";
import { getAvailability, getPricing, createBooking } from "../api/client";
import { todayStr } from "../utils/pricing";

export default function BookingPage() {
  const [selectedDate, setSelectedDate] = useState(todayStr());
  const [slots, setSlots] = useState([]);
  const [loadingSlots, setLoadingSlots] = useState(false);
  const [cart, setCart] = useState([]); // [{date, start_time, end_time}]
  const [tiers, setTiers] = useState([]);
  const [customer, setCustomer] = useState({ phone: "", name: "", email: "" });
  const [paymentMethod, setPaymentMethod] = useState("pay_on_arrival");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [successInfo, setSuccessInfo] = useState(null);

  useEffect(() => {
    getPricing().then(setTiers).catch(() => setTiers([]));
  }, []);

  useEffect(() => {
    setLoadingSlots(true);
    setError("");
    getAvailability(selectedDate)
      .then((data) => setSlots(data.slots))
      .catch(() => setError("تعذر تحميل الأوقات المتاحة."))
      .finally(() => setLoadingSlots(false));
  }, [selectedDate]);

  function toggleSlot(slot) {
    const exists = cart.find((c) => c.date === selectedDate && c.start_time === slot.start_time);
    if (exists) {
      setCart(cart.filter((c) => !(c.date === selectedDate && c.start_time === slot.start_time)));
    } else {
      setCart([...cart, { date: selectedDate, start_time: slot.start_time, end_time: slot.end_time }]);
    }
  }

  function removeFromCart(item) {
    setCart(cart.filter((c) => !(c.date === item.date && c.start_time === item.start_time)));
  }

  const selectedTimesForCurrentDate = cart
    .filter((c) => c.date === selectedDate)
    .map((c) => c.start_time);

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");

    if (cart.length === 0) {
      setError("الرجاء اختيار وقت واحد على الأقل.");
      return;
    }
    if (!customer.phone.trim()) {
      setError("رقم الهاتف إجباري.");
      return;
    }

    setSubmitting(true);
    try {
      const payload = {
        customer_phone: customer.phone.trim(),
        customer_name: customer.name.trim() || null,
        customer_email: customer.email.trim() || null,
        payment_method: paymentMethod,
        slots: cart.map((c) => ({ date: c.date, start_time: c.start_time })),
      };

      const result = await createBooking(payload);

      if (paymentMethod === "thawani" && result.payment_url) {
        window.location.href = result.payment_url;
        return;
      }

      setSuccessInfo(result);
      setCart([]);
    } catch (err) {
      setError(err?.response?.data?.message || "حدث خطأ أثناء إتمام الحجز، الرجاء المحاولة مجدداً.");
    } finally {
      setSubmitting(false);
    }
  }

  if (successInfo) {
    return (
      <div className="success-box">
        <h2>✅ تم تأكيد الحجز</h2>
        <p>رقم الحجز المرجعي: <strong>{successInfo.booking_reference}</strong></p>
        <p>الإجمالي: <strong>{Number(successInfo.total_amount).toFixed(3)} ر.ع</strong></p>
        <p className="muted">
          {successInfo.payment_method === "pay_on_arrival"
            ? "الرجاء الدفع عند الوصول للملعب."
            : "شكراً لإتمام الدفع."}
        </p>
        <button onClick={() => setSuccessInfo(null)}>حجز جديد</button>
      </div>
    );
  }

  return (
    <div className="booking-layout">
      <div className="booking-main">
        <h2>احجز ملعب البادل الآن</h2>
        <p className="muted">اختر التاريخ والوقت المناسب — يمكنك إضافة أكثر من يوم في نفس عملية الحجز.</p>

        <label className="date-picker">
          التاريخ
          <input
            type="date"
            min={todayStr()}
            value={selectedDate}
            onChange={(e) => setSelectedDate(e.target.value)}
          />
        </label>

        <TimeSlotGrid
          slots={slots}
          selectedTimes={selectedTimesForCurrentDate}
          onToggle={toggleSlot}
          loading={loadingSlots}
        />

        <form onSubmit={handleSubmit}>
          <CustomerForm
            customer={customer}
            onChange={setCustomer}
            paymentMethod={paymentMethod}
            onPaymentMethodChange={setPaymentMethod}
          />

          {error && <p className="error-msg">{error}</p>}

          <button type="submit" className="submit-btn" disabled={submitting}>
            {submitting ? "جارِ التأكيد..." : "تأكيد الحجز"}
          </button>
        </form>
      </div>

      <aside className="booking-sidebar">
        <CartPanel cart={cart} tiers={tiers} onRemove={removeFromCart} />
      </aside>
    </div>
  );
}
