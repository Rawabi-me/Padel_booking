export function pricePerHourFor(tiers, hoursCount) {
  if (!tiers || tiers.length === 0) return 0;
  const sorted = [...tiers].sort((a, b) => b.min_hours - a.min_hours);
  const match = sorted.find((t) => hoursCount >= t.min_hours);
  return match ? Number(match.price_per_hour) : Number(sorted[sorted.length - 1].price_per_hour);
}

export function todayStr() {
  const d = new Date();
  return d.toISOString().slice(0, 10);
}
