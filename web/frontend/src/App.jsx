import React from "react";
import DashboardCircle from "./components/DashboardCircle.jsx";

const sections = [
  { name: "Serveur", color: "#4f46e5", links: [{ text: "NAS", href: "#" }, { text: "Minecraft", href: "#" }] },
  { name: "Home Assistant", color: "#16a34a", links: [{ text: "Etat maison", href: "#" }, { text: "WOL", href: "#" }] },
  { name: "Réseau", color: "#eab308", links: [{ text: "Portainer", href: "#" }, { text: "cAdvisor", href: "#" }] },
  { name: "Pub", color: "#ef4444", links: [{ text: "Portfolio", href: "#" }, { text: "LinkedIn", href: "#" }] },
  { name: "Coming", color: "#0ea5e9", links: [{ text: "Soon", href: "#" }] },
];

export default function App() {
  return (
    <div className="app-center">
      <div className="container" aria-hidden={false}>
        <DashboardCircle sections={sections} />
      </div>
    </div>
  );
}
