import { BrowserRouter, Routes, Route } from "react-router-dom";
import BookingPage from "./pages/BookingPage";
import PaymentResultPage from "./pages/PaymentResultPage";
import "./App.css";

export default function App() {
  return (
    <BrowserRouter>
      <div className="app-shell" dir="rtl">
        <header className="app-header">🎾 حجز ملاعب البادل</header>
        <Routes>
          <Route path="/" element={<BookingPage />} />
          <Route path="/payment/success" element={<PaymentResultPage mode="success" />} />
          <Route path="/payment/cancel" element={<PaymentResultPage mode="cancel" />} />
        </Routes>
      </div>
    </BrowserRouter>
  );
}
