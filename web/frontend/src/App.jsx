import React from "react";
import DashboardCircle from "./components/DashboardCircle.jsx";

export default function App() {
  return (
    <div className="w-full h-full">
      <DashboardCircle onSelect={(section) => console.log(section)} />
    </div>
  );
}
