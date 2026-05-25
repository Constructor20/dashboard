import React, { useState } from "react";

export default function SectionItem({ section, x, y, onClick }) {
  const [hovered, setHovered] = useState(false);

  return (
    <div
      style={{
        position: "absolute",
        top: y,
        left: x,
        width: 100,
        height: 100,
        borderRadius: "50%",
        backgroundColor: section.color,
        cursor: "pointer",
      }}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
      onClick={onClick}
    >
      {hovered && (
        <div
          style={{
            position: "absolute",
            left: "110px",
            top: "50%",
            transform: "translateY(-50%)",
            backgroundColor: "gray",
            color: "white",
            padding: "8px",
            borderRadius: "8px",
            boxShadow: "0 4px 6px rgba(0,0,0,0.3)",
            whiteSpace: "nowrap",
          }}
        >
          {section.name}
        </div>
      )}
    </div>
  );
}
