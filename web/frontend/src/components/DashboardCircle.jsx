import React from "react";
import { AnimatePresence } from "framer-motion";
import SectionItem from "./SectionItem";

const sections = [
  { id: 1, name: "NAS", color: "#4f46e5" },
  { id: 2, name: "Serveur Minecraft", color: "#16a34a" },
  { id: 3, name: "Domotique", color: "#eab308" },
  { id: 4, name: "Monitoring", color: "#ef4444" },
  { id: 5, name: "Paramètres", color: "#0ea5e9" },
];

export default function DashboardCircle({ onSelect }) {
  const radius = 150; // rayon du cercle
  const centerX = 200;
  const centerY = 200;

  return (
    <div className="flex justify-center items-center h-screen bg-gray-900">
      <div className="relative w-[400px] h-[400px]">
        <AnimatePresence initial={false}>
          {sections.map((section, index) => {
            const angle = (index / sections.length) * 2 * Math.PI;
            const x = centerX + radius * Math.cos(angle) - 50;
            const y = centerY + radius * Math.sin(angle) - 50;

            return (
              <SectionItem
                key={section.id}
                section={section}
                x={x}
                y={y}
                onClick={() => onSelect(section)}
              />
            );
          })}
        </AnimatePresence>
      </div>
    </div>
  );
}
