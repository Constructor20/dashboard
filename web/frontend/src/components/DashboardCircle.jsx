import React, { useRef, useState, useEffect } from "react";
import { motion } from "framer-motion";
import SectionDetail from "./SectionDetail.jsx";

export default function DashboardCircle({ sections = [], outerRadius = 240, innerRadius = 160 }) {
  const items = sections;
  const count = items.length;
  const center = outerRadius;
  const angleStep = 360 / count;
  const containerRef = useRef(null);

  const [hoverIndex, setHoverIndex] = useState(null);
  const [selectedIndex, setSelectedIndex] = useState(null);
  const [showDetail, setShowDetail] = useState(false);
  const [containerRect, setContainerRect] = useState(null); // mémorisation du rect

  // On capture le rect après le premier rendu pour stabiliser le calcul
  useEffect(() => {
    if (containerRef.current) {
      setContainerRect(containerRef.current.getBoundingClientRect());
    }
  }, []);

  const polarToCartesian = (cx, cy, r, angleDeg) => {
    const a = ((angleDeg - 90) * Math.PI) / 180;
    return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
  };

  const describeArc = (cx, cy, rOuter, rInner, startAngle, endAngle) => {
    const startOuter = polarToCartesian(cx, cy, rOuter, endAngle);
    const endOuter = polarToCartesian(cx, cy, rOuter, startAngle);
    const startInner = polarToCartesian(cx, cy, rInner, startAngle);
    const endInner = polarToCartesian(cx, cy, rInner, endAngle);
    const large = endAngle - startAngle <= 180 ? "0" : "1";
    return [
      `M ${startOuter.x} ${startOuter.y}`,
      `A ${rOuter} ${rOuter} 0 ${large} 0 ${endOuter.x} ${endOuter.y}`,
      `L ${startInner.x} ${startInner.y}`,
      `A ${rInner} ${rInner} 0 ${large} 1 ${endInner.x} ${endInner.y}`,
      "Z",
    ].join(" ");
  };

  const computeHoverOffset = (midAngle) => {
    const mid = polarToCartesian(center, center, (outerRadius + innerRadius) / 2, midAngle);
    const dx = mid.x - center;
    const dy = mid.y - center;
    return { x: dx * 0.12, y: dy * 0.12 };
  };

  // version corrigée pour éviter le "recadrage fantôme"
  const computeMoveToCorner = (midAngle) => {
    if (!containerRect) return { xKF: [0, 0], yKF: [0, 0] };

    const mid = polarToCartesian(center, center, (outerRadius + innerRadius) / 2, midAngle);
    const padding = 32;

    const currentX = containerRect.left + mid.x;
    const currentY = containerRect.top + mid.y;

    const targetX = padding - currentX;
    const targetY = padding - currentY;

    return { xKF: [0, targetX], yKF: [0, targetY] };
  };

  const onClickSector = (i, midAngle) => {
    setSelectedIndex(i);
    setShowDetail(false);
    const { xKF, yKF } = computeMoveToCorner(midAngle);

    setTimeout(() => {
      setShowDetail(true);
    }, 3000);
  };

  return (
    <div ref={containerRef} style={{ width: outerRadius * 2, height: outerRadius * 2, position: "relative" }}>
      <svg width={outerRadius * 2} height={outerRadius * 2} viewBox={`0 0 ${outerRadius * 2} ${outerRadius * 2}`} style={{ overflow: "visible" }}>
        {items.map((it, idx) => {
          const start = idx * angleStep;
          const end = (idx + 1) * angleStep;
          const mid = (start + end) / 2;
          const d = describeArc(center, center, outerRadius, innerRadius, start, end);
          const isSelected = selectedIndex === idx;
          const { xKF, yKF } = computeMoveToCorner(mid);
          const desiredAngle = 135;
          const rotationDelta = desiredAngle - mid;

          return (
            <motion.path
              key={it.name}
              d={d}
              fill={it.color}
              stroke="rgba(0,0,0,0.25)"
              strokeWidth={1}
              style={{ transformOrigin: `${center}px ${center}px`, cursor: selectedIndex == null ? "pointer" : "default" }}
              initial={{ opacity: 1 }}
              animate={
                isSelected
                  ? { x: xKF, y: yKF, scale: [1, 1.06, 1.35], rotate: [0, rotationDelta], opacity: 1 }
                  : { x: 0, y: 0, scale: 1, rotate: 0, opacity: selectedIndex == null ? 1 : 0 }
              }
              transition={
                isSelected
                  ? { x: { duration: 3, ease: "easeInOut" }, y: { duration: 3, ease: "easeInOut" }, rotate: { duration: 3, ease: "easeInOut" }, scale: { duration: 3, ease: [0.22, 1, 0.36, 1] } }
                  : { type: "spring", stiffness: 160, damping: 20 }
              }
              onMouseEnter={() => { if (selectedIndex == null) setHoverIndex(idx); }}
              onMouseLeave={() => { if (selectedIndex == null) setHoverIndex((h) => (h === idx ? null : h)); }}
              onClick={() => { if (selectedIndex == null) onClickSector(idx, mid); }}
            />
          );
        })}
      </svg>

      {hoverIndex != null && selectedIndex == null && (() => {
        const s = items[hoverIndex];
        const start = hoverIndex * angleStep;
        const end = (hoverIndex + 1) * angleStep;
        const mid = (start + end) / 2;
        const labelPos = polarToCartesian(center, center, outerRadius + 18, mid);
        return <div className="label" style={{ left: labelPos.x + 8, top: labelPos.y - 18 }}>{s.name}</div>;
      })()}

      {showDetail && selectedIndex != null && (
        <div className="detail-panel" aria-hidden={!showDetail}>
          <SectionDetail
            item={items[selectedIndex]}
            onClose={() => { setShowDetail(false); setSelectedIndex(null); }}
          />
        </div>
      )}
    </div>
  );
}
