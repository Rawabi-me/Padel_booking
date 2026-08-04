import { useEffect, useState } from "react";
import { useSearchParams, Link } from "react-router-dom";
import { verifyPayment } from "../api/client";

export default function PaymentResultPage({ mode }) {
  const [searchParams] = useSearchParams();
  const ref = searchParams.get("ref");
  const [status, setStatus] = useState("جارِ التحقق من حالة الدفع...");
  const [ok, setOk] = useState(null);

  useEffect(() => {
    if (!ref) {
      setStatus("لم يتم العثور على رقم الحجز.");
      setOk(false);
      return;
    }

    verifyPayment(ref)
      .then((data) => {
        if (data.payment_status === "paid") {
          setStatus("تم الدفع بنجاح! تم تأكيد حجزك.");
          setOk(true);
        } else if (data.payment_status === "failed") {
          setStatus("فشلت عملية الدفع أو انتهت صلاحيتها.");
          setOk(false);
        } else {
          setStatus("الدفع قيد المعالجة، الرجاء المحاولة بعد لحظات.");
          setOk(null);
        }
      })
      .catch(() => {
        setStatus("تعذر التحقق من حالة الدفع.");
        setOk(false);
      });
  }, [ref]);

  return (
    <div className="success-box">
      <h2>{mode === "cancel" ? "تم إلغاء الدفع" : "نتيجة الدفع"}</h2>
      {ref && <p>رقم الحجز المرجعي: <strong>{ref}</strong></p>}
      <p className={ok === false ? "error-msg" : ""}>{status}</p>
      <Link to="/">العودة للصفحة الرئيسية</Link>
    </div>
  );
}
