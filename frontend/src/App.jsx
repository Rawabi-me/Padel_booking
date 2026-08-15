import { BrowserRouter, Routes, Route, Link } from "react-router-dom";
import BookingPage from "./pages/BookingPage";
import PaymentResultPage from "./pages/PaymentResultPage";
import TrackBookingPage from "./pages/TrackBookingPage";
import "./App.css";

export default function App() {
  return (
    <BrowserRouter>
      {/* تم جعل app-shell يضم جميع العناصر بداخله بشكل صحيح */}
      <div className="app-shell" dir="rtl">
        <header className="app-header">
          🎾 حجز ملاعب البادل
          {/* استخدام Link بدلاً من a لمنع إعادة تحميل الصفحة */}
          <Link to="/track" style={{ float: "left", color: "#fff", fontSize: "0.9rem", textDecoration: "none" }}>
            تتبع حجزك
          </Link>
        </header>

        <Routes>
          <Route path="/" element={<BookingPage />} />
          <Route path="/payment/success" element={<PaymentResultPage mode="success" />} />
          <Route path="/payment/cancel" element={<PaymentResultPage mode="cancel" />} />
          <Route path="/track" element={<TrackBookingPage />} />
        </Routes>
      </div>
    </BrowserRouter>
  );
}
