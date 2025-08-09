import React from "react";
import { motion } from "framer-motion";

export default function SectionDetail({ section, onBack }) {
  return (
    <motion.div
      className="fixed inset-0 bg-gray-900 flex flex-col items-center justify-center text-white"
      initial={{ opacity: 0, scale: 0.8 }}
      animate={{ opacity: 1, scale: 1 }}
      exit={{ opacity: 0, scale: 0.8 }}
      transition={{ duration: 0.5 }}
    >
      <h1 className="text-5xl font-bold mb-6">{section.name}</h1>
      <p className="text-lg mb-8">Ici tu peux mettre les liens (NAS, Minecraft, etc.)</p>
      <button
        onClick={onBack}
        className="px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-lg shadow-lg"
      >
        Retour
      </button>
    </motion.div>
  );
}
