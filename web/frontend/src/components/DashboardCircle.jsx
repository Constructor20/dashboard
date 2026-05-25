import React, { useRef, useState, useEffect } from "react";
import { motion } from "framer-motion";
import SectionDetail from "./SectionDetail.jsx";

export default function DashboardCircle({ sections = [], outerRadius = 240, innerRadius = 160, animEnabled = true }) {
  const items = sections;
  const count = items.length;
  const center = outerRadius;
  const angleStep = 360 / count;
  const containerRef = useRef(null);
  const timerRef = useRef(null);

  const [hoverIndex, setHoverIndex] = useState(null);
  const [selectedIndex, setSelectedIndex] = useState(null);
  const [showDetail, setShowDetail] = useState(false);
  const [animating, setAnimating] = useState(false);
  const [containerRect, setContainerRect] = useState(null);

  useEffect(() => {
    if (containerRef.current) {
      setContainerRect(containerRef.current.getBoundingClientRect());
    }
    return () => { if (timerRef.current) clearTimeout(timerRef.current); };
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

  const skipAnimation = () => {
    if (timerRef.current) clearTimeout(timerRef.current);
    setShowDetail(true);
    setAnimating(false);
  };

  const onClickSector = (i, midAngle) => {
    setSelectedIndex(i);
    setShowDetail(false);
    computeMoveToCorner(midAngle);

    if (animEnabled) {
      setAnimating(true);
      timerRef.current = setTimeout(() => {
        setShowDetail(true);
        setAnimating(false);
      }, 3000);
    } else {
      setShowDetail(true);
    }
  };

  const onCloseDetail = () => {
    setShowDetail(false);
    setSelectedIndex(null);
    setAnimating(false);
    if (timerRef.current) clearTimeout(timerRef.current);
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
              stroke={hoverIndex === idx ? "rgba(0,212,255,0.6)" : "rgba(0,0,0,0.25)"}
              strokeWidth={hoverIndex === idx ? 2 : 1}
              style={{
                transformOrigin: `${center}px ${center}px`,
                cursor: selectedIndex == null ? "pointer" : "default",
                filter: hoverIndex === idx ? "drop-shadow(0 0 8px rgba(0,212,255,0.6))" : "none",
              }}
              initial={{ opacity: 1 }}
              animate={
                isSelected
                  ? { x: xKF, y: yKF, scale: [1, 1.06, 1.35], rotate: [0, rotationDelta], opacity: 1 }
                  : { x: 0, y: 0, scale: hoverIndex === idx ? 1.05 : 1, rotate: 0, opacity: selectedIndex == null ? 1 : 0 }
              }
              transition={
                isSelected
                  ? { x: { duration: animEnabled ? 3 : 0 }, y: { duration: animEnabled ? 3 : 0 }, rotate: { duration: animEnabled ? 3 : 0 }, scale: { duration: animEnabled ? 3 : 0, ease: [0.22, 1, 0.36, 1] } }
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

      {animating && (
        <div className="skip-btn" onClick={skipAnimation}>
          <span>SKIP</span>
        </div>
      )}

      {showDetail && selectedIndex != null && (
        <div className="detail-panel" aria-hidden={!showDetail}>
          <SectionDetail
            item={items[selectedIndex]}
            onClose={onCloseDetail}
          />
        </div>
      )}
    </div>
  );
}
