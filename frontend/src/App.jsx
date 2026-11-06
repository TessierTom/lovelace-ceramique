import { useState } from "react";
import "./App.css";
  import Kanban from "./components/Kanban/Kanban";

function App() {
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);
  const [status, setStatus] = useState("");

  async function verifierConnexion() {
    setLoading(true);
    setMessage("");
    setStatus("");

    try {
      const response = await fetch("/api/healthcheck");

      if (!response.ok) {
        throw new Error("Erreur de connexion au serveur");
      }

      const data = await response.json();

      if (data.status === "ok" && data.database === "connected") {
        setMessage(data.message);
        setStatus("success");
      } else {
        throw new Error("La base de données est indisponible");
      }
    } catch (error) {
      setMessage(error.message);
      setStatus("error");
    } finally {
      setLoading(false);
    }
  }

  return (
    <main className="app">
      <header>
        <h1>Lovelace Céramique</h1>
        <p>Gestion de production pour les ateliers de céramique</p>
      </header>

      <section>
        <h2>Bienvenue dans votre atelier</h2>

        <p>
          Suivez vos objets, vos étapes de fabrication et vos tâches de
          production.
        </p>

        <button type="button" onClick={verifierConnexion} disabled={loading}>
          {loading ? "Vérification..." : "Vérifier la connexion"}
        </button>

        {message && (
          <p role="status" className={`connection-message ${status}`}>
            {message}
          </p>
        )}
      </section>
              <Kanban />
    </main>
  );
}

export default App;
