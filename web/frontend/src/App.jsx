import React from "react";
import DashboardCircle from "./components/DashboardCircle.jsx";

const sections = [
  { name: "Serveur", color: "#00d4ff", links: [{ text: "Minecraft Panel", href: "https://php.chrisdashboard.ddnsfree.com" }] },
  { name: "Services", color: "#ff6b35", links: [{ text: "Voir les services", href: "/internal.html" }] },
  { name: "Home Assistant", color: "#00ff88", links: [{ text: "Etat maison", href: "#" }, { text: "WOL", href: "#" }] },
  { name: "Réseau", color: "#ffd700", links: [{ text: "cAdvisor", href: "#" }, { text: "Monitoring", href: "#" }] },
  { name: "Portfolio", color: "#ff2d55", links: [{ text: "Mon Portfolio", href: "/portfolio/index.html" }, { text: "LinkedIn", href: "https://www.linkedin.com/in/christophe-aleixo-14b5a4256" }, { text: "GitHub", href: "https://github.com/Constructor20" }] },
];

const now = new Date();
const timeStr = now.toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" });
const dateStr = now.toLocaleDateString("fr-FR", { day: "numeric", month: "long", year: "numeric" });

function getCookie(name) {
  const match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
  return match ? match[2] : null;
}

function setCookie(name, value, days) {
  const d = new Date();
  d.setTime(d.getTime() + days * 86400000);
  document.cookie = name + "=" + value + ";path=/;expires=" + d.toUTCString();
}

export default function App() {
  const [time, setTime] = React.useState(timeStr);
  const [animEnabled, setAnimEnabled] = React.useState(() => {
    const saved = getCookie("anim");
    return saved !== null ? saved === "1" : true;
  });

  React.useEffect(() => {
    const timer = setInterval(() => {
      const n = new Date();
      setTime(n.toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit", second: "2-digit" }));
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  React.useEffect(() => {
    setCookie("anim", animEnabled ? "1" : "0", 365);
    document.documentElement.dataset.anim = animEnabled ? "on" : "off";
  }, [animEnabled]);

  const toggleAnim = () => setAnimEnabled((v) => !v);

  return (
    <div className="app-center">
      <div className="hud-top">
        <div className="hud-brand">
          <span className="hud-jarvis">J.A.R.V.I.S</span>
          <span className="hud-sub">System Dashboard v2.1</span>
        </div>
        <div className="hud-status">
          <div className="hud-indicator">
            <span className="status-dot"></span>
            <span>SYSTEM ONLINE</span>
          </div>
          <div className="hud-time">{time}</div>
        </div>
      </div>

      <div className="anim-toggle" onClick={toggleAnim}>
        <span className="anim-toggle-label">ANIM</span>
        <span className={`anim-toggle-switch ${animEnabled ? "on" : "off"}`}></span>
      </div>

      <div className="scanline"></div>

      <div className="container" aria-hidden={false}>
        <DashboardCircle sections={sections} animEnabled={animEnabled} />
      </div>

      <div className="hud-bottom">
        <span className="hud-bottom-item">chrisdashboard.ddnsfree.com</span>
        <span className="hud-bottom-item">{dateStr}</span>
        <span className="hud-bottom-item">SECTEUR: {navigator.onLine ? "CONNECTÉ" : "HORS LIGNE"}</span>
      </div>
    </div>
  );
}