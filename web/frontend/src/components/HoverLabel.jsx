import React from "react";
import { motion } from "framer-motion";

export default function HoverLabel({ text }) {
  return (
    <motion.div
      className="absolute left-[110px] top-1/2 -translate-y-1/2 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-md"
      initial={{ opacity: 0, x: -10 }}
      animate={{ opacity: 1, x: 0 }}
      exit={{ opacity: 0, x: -10 }}
      transition={{ duration: 0.2 }}
    >
      {text}
    </motion.div>
  );
}
