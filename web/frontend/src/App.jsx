import React from "react";
import DashboardCircle from "./components/DashboardCircle.jsx";

const sections = [
  { name: "Serveur", color: "#4f46e5", links: [{ text: "NAS", href: "#" }, { text: "Minecraft", href: "https://php.chrisdashboard.ddnsfree.com" }] },
  { name: "Home Assistant", color: "#16a34a", links: [{ text: "Etat maison", href: "#" }, { text: "WOL", href: "#" }] },
  { name: "Réseau", color: "#eab308", links: [{ text: "Portainer", href: "http://100.90.244.79:9000" }, { text: "cAdvisor", href: "#" }] },
  { name: "Pub", color: "#ef4444", links: [{ text: "Portfolio", href: "#" }, { text: "LinkedIn", href: "https://www.linkedin.com/in/christophe-aleixo-14b5a4256" }] },
  { name: "Coming", color: "#0ea5e9", links: [{ text: "Soon", href: "#" }] },
];

export default function App() {
  return (
    <div className="app-center">
      <div className="hover-message">
          Passez la souris sur une section pour voir les options disponibles
      </div>
      <div className="container" aria-hidden={false}>
        <DashboardCircle sections={sections} />
      </div>
    </div>
  );
}
