export default function CustomerForm({ customer, onChange, paymentMethod, onPaymentMethodChange }) {
  return (
    <div className="customer-form">
      <h3>بياناتك</h3>
      <label>
        رقم الهاتف <span className="required">*</span>
        <input
          type="tel"
          required
          value={customer.phone}
          onChange={(e) => onChange({ ...customer, phone: e.target.value })}
          placeholder="9XXXXXXX"
        />
      </label>
      <label>
        الاسم (اختياري)
        <input
          type="text"
          value={customer.name}
          onChange={(e) => onChange({ ...customer, name: e.target.value })}
        />
      </label>
      <label>
        البريد الإلكتروني (اختياري)
        <input
          type="email"
          value={customer.email}
          onChange={(e) => onChange({ ...customer, email: e.target.value })}
        />
      </label>

      <div className="payment-methods">
        <p>طريقة الدفع</p>
        <label className="radio">
          <input
            type="radio"
            name="payment_method"
            checked={paymentMethod === "pay_on_arrival"}
            onChange={() => onPaymentMethodChange("pay_on_arrival")}
          />
          الدفع عند الوصول
        </label>
        <label className="radio">
          <input
            type="radio"
            name="payment_method"
            checked={paymentMethod === "thawani"}
            onChange={() => onPaymentMethodChange("thawani")}
          />
          الدفع الإلكتروني (ثواني)
        </label>
      </div>
    </div>
  );
}
