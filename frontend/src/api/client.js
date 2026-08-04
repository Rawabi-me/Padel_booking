import axios from "axios";

const client = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8000/api",
});

export const getAvailability = (date) =>
  client.get("/availability", { params: { date } }).then((r) => r.data);

export const getPricing = () => client.get("/pricing").then((r) => r.data);

export const createBooking = (payload) =>
  client.post("/bookings", payload).then((r) => r.data);

export const getBooking = (reference) =>
  client.get(`/bookings/${reference}`).then((r) => r.data);

export const verifyPayment = (reference) =>
  client.get("/payment/verify", { params: { ref: reference } }).then((r) => r.data);

export default client;
