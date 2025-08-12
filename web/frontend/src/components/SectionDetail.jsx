import React from "react";
import { motion } from "framer-motion";

export default function SectionDetail({ item, onClose }) {
  if (!item) return null;

  return (
    <motion.div
      className="panel-card"
      initial={{ opacity: 0, x: -20 }}
      animate={{ opacity: 1, x: 0 }}
      exit={{ opacity: 0, x: -20 }}
    >
      <button className="button" onClick={onClose}>← Retour</button>

      <h2 style={{ fontSize: 26, marginTop: 12 }}>{item.name}</h2>
      <p style={{ opacity: 0.85, marginTop: 8 }}>{`Actions & redirections pour ${item.name}`}</p>

      <div style={{ marginTop: 14, display: "flex", flexDirection: "column", gap: 10 }}>
        {item.links?.map((l, i) => (
          <a key={i} href={l.href} style={{ padding: "10px 12px", borderRadius: 8, background: "rgba(255,255,255,0.02)", color: "white", textDecoration: "none", border: "1px solid rgba(255,255,255,0.03)" }}>
            {l.text}
          </a>
        ))}
      </div>
    </motion.div>
  );
}
