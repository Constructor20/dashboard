import React, { useRef, useState } from "react";
import { motion } from "framer-motion";
import SectionDetail from "./SectionDetail.jsx";

/**
 * DashboardCircle
 * - sections: array {name,color,links}
 * - outerRadius / innerRadius in px
 *
 * Implementation notes:
 * - draws donut sectors with SVG paths
 * - hover: small radial translation + label shown
 * - click: animate selected sector toward top-left corner (curve-like feel via x/y keyframes + scale) for duration ~3s
 *         other sectors fade out
 * - at animation end the detail panel is shown (same page)
 */

export default function DashboardCircle({ sections = [], outerRadius = 240, innerRadius = 160 }) {
  const items = sections;
  const count = items.length;
  const center = outerRadius; // svg coordinate center
  const angleStep = 360 / count;
  const containerRef = useRef(null);

  const [hoverIndex, setHoverIndex] = useState(null);
  const [selectedIndex, setSelectedIndex] = useState(null);
  const [showDetail, setShowDetail] = useState(false);

  // convert degrees to radians then polar -> cartesian
  const polarToCartesian = (cx, cy, r, angleDeg) => {
    const a = ((angleDeg - 90) * Math.PI) / 180;
    return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
  };

  // make a donut sector path from startAngle to endAngle
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

  // compute small radial offset for hover
  const computeHoverOffset = (midAngle) => {
    const mid = polarToCartesian(center, center, (outerRadius + innerRadius) / 2, midAngle);
    const dx = mid.x - center;
    const dy = mid.y - center;
    // small outward offset (px)
    return { x: dx * 0.12, y: dy * 0.12 };
  };

  // compute keyframes to "arc" toward corner (top-left here)
  // we compute relative x/y in svg coords: start at 0, middle small outward step, end at target
  const computeMoveToCorner = (midAngle) => {
    // compute small outward vector
    const mid = polarToCartesian(center, center, (outerRadius + innerRadius) / 2, midAngle);
    const dx = mid.x - center;
    const dy = mid.y - center;
    const step1 = { x: dx * 0.12, y: dy * 0.12 };

    // compute target relative to svg container so the sector ends near top-left of viewport with padding
    // get container position on screen
    const rect = containerRef.current?.getBoundingClientRect();
    // fallback target in svg coords if no rect
    let targetX = -outerRadius * 1.6;
    let targetY = -outerRadius * 1.2;
    if (rect) {
      // want target at 32px from left and 32px from top of viewport
      // compute where svg's origin is on screen and convert
      const svgLeft = rect.left;
      const svgTop = rect.top;
      // target in screen coords
      const screenTargetX = 32;
      const screenTargetY = 32;
      // convert to svg-local translation (approx): translateX = screenTargetX - (svgLeft + center)
      targetX = screenTargetX - (svgLeft + center);
      targetY = screenTargetY - (svgTop + center);
    }

    // end keyframes
    const endX = targetX;
    const endY = targetY;

    // keyframes [start, bump out, end]
    return {
      xKF: [0, step1.x, endX],
      yKF: [0, step1.y, endY],
    };
  };

  // when clicking a sector
  const onClickSector = (i, midAngle) => {
    setSelectedIndex(i);
    setShowDetail(false);

    // if containerRef present, compute movement then wait for animation end
    const { xKF, yKF } = computeMoveToCorner(midAngle);

    // use setTimeout to show detail after animation duration
    setTimeout(() => {
      setShowDetail(true);
    }, 3000);
  };

  return (
    <>
      <div ref={containerRef} style={{ width: outerRadius * 2, height: outerRadius * 2, position: "relative" }}>
        <svg width={outerRadius * 2} height={outerRadius * 2} viewBox={`0 0 ${outerRadius * 2} ${outerRadius * 2}`}>
          {/* We do not render a filled center: keep it empty to match spec */}
          {/* draw each sector */}
          {items.map((it, idx) => {
            const start = idx * angleStep;
            const end = (idx + 1) * angleStep;
            const mid = (start + end) / 2;
            const d = describeArc(center, center, outerRadius, innerRadius, start, end);
            const hoverOffset = computeHoverOffset(mid);
            const isSelected = selectedIndex === idx;

            // compute final keyframes if selected
            const { xKF, yKF } = computeMoveToCorner(mid);

            return (
              <motion.path
                key={it.name}
                d={d}
                fill={it.color}
                stroke="rgba(0,0,0,0.25)"
                strokeWidth={1}
                style={{ transformOrigin: `${center}px ${center}px`, cursor: "pointer" }}
                initial={{ opacity: 1 }}
                animate={
                  isSelected
                    ? {
                        x: xKF,
                        y: yKF,
                        scale: [1, 1.06, 1.95],
                        opacity: 1,
                      }
                    : hoverIndex != null && hoverIndex !== idx
                    ? { opacity: 0, scale: 1 } // other sectors fade when one selected/animating
                    : hoverIndex === idx
                    ? { x: hoverOffset.x, y: hoverOffset.y, scale: 1.02 }
                    : { x: 0, y: 0, scale: 1, opacity: 1 }
                }
                transition={
                  isSelected
                    ? { x: { duration: 3, ease: "easeInOut" }, y: { duration: 3, ease: "easeInOut" }, scale: { duration: 3, ease: [0.22, 1, 0.36, 1] } }
                    : { type: "spring", stiffness: 160, damping: 20 }
                }
                onMouseEnter={() => setHoverIndex(idx)}
                onMouseLeave={() => setHoverIndex((h) => (h === idx ? null : h))}
                onClick={() => onClickSector(idx, mid)}
              />
            );
          })}
        </svg>

        {/* label hover (absolute div) */}
        {hoverIndex != null && (() => {
          const s = items[hoverIndex];
          const start = hoverIndex * angleStep;
          const end = (hoverIndex + 1) * angleStep;
          const mid = (start + end) / 2;
          const labelPos = polarToCartesian(center, center, outerRadius + 18, mid);
          // place label relative to svg container
          return (
            <div className="label" style={{ left: labelPos.x + 8, top: labelPos.y - 18 }}>
              {s.name}
            </div>
          );
        })()}

        {/* detail panel that appears after animation */}
        {showDetail && selectedIndex != null && (
          <div className="detail-panel" aria-hidden={!showDetail}>
            <SectionDetail
              item={items[selectedIndex]}
              onClose={() => {
                setShowDetail(false);
                setSelectedIndex(null);
              }}
            />
          </div>
        )}
      </div>
    </>
  );
}
