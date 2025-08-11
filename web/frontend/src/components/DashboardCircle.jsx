// src/components/DashboardCircle.jsx
import React, { useState, useRef } from "react";
import { motion } from "framer-motion";

/**
 * DashboardCircle
 * props:
 *  - sections: [{ name, color, links: [{text, href}, ...] }, ...]
 *  - outerRadius, innerRadius : sizes en px
 *
 * comportement :
 *  - hover => écarte légèrement la part
 *  - click => animation (3s) : trajectoire type arc vers la gauche + zoom
 *            puis affiche panel detail (sommaire) sur la même page
 */

export default function DashboardCircle({
  sections = null,
  outerRadius = 240,
  innerRadius = 110,
}) {
  const defaultSections = [
    { name: "Serveur", color: "#4f46e5", links: [{ text: "NAS", href: "#" }, { text: "Minecraft", href: "#" }] },
    { name: "Home Assistant", color: "#16a34a", links: [{ text: "Etat maison", href: "#" }, { text: "WOL", href: "#" }] },
    { name: "Réseau", color: "#eab308", links: [{ text: "Portainer", href: "#" }, { text: "cAdvisor", href: "#" }] },
    { name: "Pub", color: "#ef4444", links: [{ text: "Portfolio", href: "#" }, { text: "LinkedIn", href: "#" }] },
    { name: "Coming", color: "#0ea5e9", links: [{ text: "Soon", href: "#" }] },
  ];

  const items = sections || defaultSections;
  const center = outerRadius;
  const anglePer = 360 / items.length;
  const [hoverIndex, setHoverIndex] = useState(null);
  const [selectedIndex, setSelectedIndex] = useState(null);
  const [showDetail, setShowDetail] = useState(false);
  const animTimeoutRef = useRef(null);

  // convertit angle en coord cartésiennes
  const polarToCartesian = (cx, cy, r, angleDeg) => {
    const a = (angleDeg - 90) * (Math.PI / 180);
    return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
  };

  // dessine un segment 'donut' (arc extérieur -> arc intérieur)
  const describeArc = (cx, cy, rOuter, rInner, startAngle, endAngle) => {
    const startOuter = polarToCartesian(cx, cy, rOuter, endAngle);
    const endOuter = polarToCartesian(cx, cy, rOuter, startAngle);
    const startInner = polarToCartesian(cx, cy, rInner, startAngle);
    const endInner = polarToCartesian(cx, cy, rInner, endAngle);
    const largeArc = endAngle - startAngle <= 180 ? "0" : "1";

    return [
      `M ${startOuter.x} ${startOuter.y}`,
      `A ${rOuter} ${rOuter} 0 ${largeArc} 0 ${endOuter.x} ${endOuter.y}`,
      `L ${startInner.x} ${startInner.y}`,
      `A ${rInner} ${rInner} 0 ${largeArc} 1 ${endInner.x} ${endInner.y}`,
      "Z",
    ].join(" ");
  };

  // Calcule keyframes x/y pour donner une trajectoire d'arc vers la gauche
  // on retourne [xKeyframes], [yKeyframes] en px relatifs.
  const computeArcKeyframes = (midAngle) => {
    // point central de la part (sur la moyenne radius)
    const mid = polarToCartesian(center, center, (outerRadius + innerRadius) / 2, midAngle);
    const dirX = mid.x - center;
    const dirY = mid.y - center;

    // step1 : léger écart extérieur (hover-like)
    const step1 = { x: dirX * 0.12, y: dirY * 0.12 };
    // step2 : mouvement vers la gauche + légère courbe (milieu)
    // on veut finir bien à gauche (ex: -outerRadius*1.8) et légèrement haut ou bas dépendant
    const endX = -outerRadius * 2.2; // assez hors écran à gauche
    const endY = -dirY * 0.3; // donne une courbure selon la partie

    // keyframes (0 -> step1 -> end)
    const xKF = [0, step1.x, endX];
    const yKF = [0, step1.y, endY];

    return { xKF, yKF };
  };

  const onClickPart = (i) => {
    // annule ancienne animation pending
    if (animTimeoutRef.current) {
      clearTimeout(animTimeoutRef.current);
      animTimeoutRef.current = null;
    }

    setSelectedIndex(i);
    setShowDetail(false); // caché pendant animation

    // après la durée de l'animation (3s), afficher le panneau detail
    animTimeoutRef.current = setTimeout(() => {
      setShowDetail(true);
      animTimeoutRef.current = null;
    }, 3000); // 3000 ms = durée animation
  };

  const onCloseDetail = () => {
    if (animTimeoutRef.current) {
      clearTimeout(animTimeoutRef.current);
      animTimeoutRef.current = null;
    }
    setShowDetail(false);
    // remettre tout à zéro (retour)
    setSelectedIndex(null);
  };

  return (
    <div className="w-full min-h-screen flex items-center justify-center bg-[#0b1020] text-white">
      <div style={{ position: "relative", width: outerRadius * 2, height: outerRadius * 2 }}>
        <svg
          width={outerRadius * 2}
          height={outerRadius * 2}
          viewBox={`0 0 ${outerRadius * 2} ${outerRadius * 2}`}
          style={{ overflow: "visible", display: "block" }}
        >
          {/* centre (optionnel) */}
          <circle cx={center} cy={center} r={innerRadius - 6} fill="#071026" />

          {items.map((s, idx) => {
            const start = idx * anglePer;
            const end = (idx + 1) * anglePer;
            const mid = (start + end) / 2;
            const d = describeArc(center, center, outerRadius, innerRadius, start, end);

            // hover offset vector for small separation
            const midPoint = polarToCartesian(center, center, (outerRadius + innerRadius) / 2, mid);
            const dirX = midPoint.x - center;
            const dirY = midPoint.y - center;

            const isHover = hoverIndex === idx;
            const isSelected = selectedIndex === idx;

            // compute arc keyframes for clicked animation
            const { xKF, yKF } = computeArcKeyframes(mid);

            return (
              <motion.path
                key={s.name}
                d={d}
                fill={s.color}
                stroke="#0b0f18"
                strokeWidth={2}
                style={{ transformOrigin: `${center}px ${center}px`, cursor: "pointer" }}
                // small hover translation outward
                animate={
                  isSelected
                    ? { x: xKF, y: yKF, scale: [1, 1.1, 2.2] } // keyframes scale too
                    : isHover
                    ? { x: dirX * 0.12, y: dirY * 0.12, scale: 1.05 }
                    : { x: 0, y: 0, scale: 1 }
                }
                transition={
                  isSelected
                    ? { x: { duration: 3, ease: "easeInOut" }, y: { duration: 3, ease: "easeInOut" }, scale: { duration: 3, ease: [0.22, 1, 0.36, 1] } }
                    : { type: "spring", stiffness: 160, damping: 18 }
                }
                onMouseEnter={() => setHoverIndex(idx)}
                onMouseLeave={() => setHoverIndex((h) => (h === idx ? null : h))}
                onClick={() => onClickPart(idx)}
              />
            );
          })}
        </svg>

        {/* Sommaire / panel detail (apparaît quand showDetail === true) */}
        <div
          aria-hidden={!showDetail}
          style={{
            position: "absolute",
            top: 0,
            left: 0,
            width: outerRadius * 2,
            height: outerRadius * 2,
            display: showDetail ? "flex" : "none",
            alignItems: "center",
            justifyContent: "flex-start",
            pointerEvents: showDetail ? "auto" : "none",
          }}
        >
          <div
            style={{
              width: "45%",
              minWidth: 360,
              marginLeft: 24,
              background: "linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02))",
              borderRadius: 12,
              padding: 24,
              boxShadow: "0 10px 30px rgba(0,0,0,0.6)",
              backdropFilter: "blur(6px)",
            }}
          >
            <button
              onClick={onCloseDetail}
              style={{
                padding: "8px 12px",
                borderRadius: 8,
                background: "#0b1220",
                color: "#fff",
                border: "1px solid rgba(255,255,255,0.05)",
                cursor: "pointer",
                marginBottom: 12,
              }}
            >
              ← Retour
            </button>

            {selectedIndex != null && (
              <>
                <h2 style={{ fontSize: 28, margin: "8px 0 12px 0" }}>{items[selectedIndex].name}</h2>
                <p style={{ opacity: 0.8, marginBottom: 16 }}>
                  Sommaire — liens et actions pour <strong>{items[selectedIndex].name}</strong>
                </p>

                <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                  {items[selectedIndex].links?.map((l, i) => (
                    <a
                      key={i}
                      href={l.href}
                      style={{
                        padding: "10px 12px",
                        borderRadius: 8,
                        background: "rgba(255,255,255,0.02)",
                        color: "#fff",
                        textDecoration: "none",
                        border: "1px solid rgba(255,255,255,0.03)",
                      }}
                    >
                      {l.text}
                    </a>
                  ))}
                </div>
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
