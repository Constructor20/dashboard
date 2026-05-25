import React from "react";
import { motion } from "framer-motion";

export default function SectionDetail({ item, onClose }) {
  if (!item) return null;

  return (
    <motion.div
      className="panel-card"
      initial={{ opacity: 0, y: 20, scale: 0.95 }}
      animate={{ opacity: 1, y: 0, scale: 1 }}
      exit={{ opacity: 0, y: 20, scale: 0.95 }}
      transition={{ duration: 0.3, ease: "easeOut" }}
    >
      <button className="button" onClick={onClose}>← RETOUR</button>

      <h2>{item.name}</h2>
      <p className="panel-sub">{`ACCÈS RAPIDE — ${item.name.toUpperCase()}`}</p>

      <div className="links-section">
        {item.links?.map((l, i) => (
          <a key={i} href={l.href}>
            <span style={{ color: "var(--cyan)", marginRight: 10 }}>▸</span>
            {l.text}
          </a>
        ))}
      </div>
    </motion.div>
  );
}
