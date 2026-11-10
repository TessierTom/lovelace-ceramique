import { useState } from "react";
import InscriptionForm from "../InscriptionForm/InscriptionForm";
import FormulaireConnexion from "../FormulaireConnexion/FormulaireConnexion";

function Accueil() {
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);
  const [status, setStatus] = useState("");
  const [utilisateurs, setUtilisateurs] = useState([]);

  async function verifierConnexion() {
    setLoading(true);
    setMessage("");
    setStatus("");

    try {
      const response = await fetch("/api/healthcheck", {
        method: "GET",
        headers: {
          "Content-Type": "application/json"
        }
      });

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

  async function getUtilisateurs() {
    setLoading(true);
    setMessage("");
    setStatus("");

    try {
      const response = await fetch("/api/utilisateur", {
        method: "GET",
        headers: {
          "Content-Type": "application/json"
        }
      });

      if (!response.ok) {
        throw new Error("Erreur lors de la récupération des utilisateurs");
      }

      const data = await response.json();

      if (data.status === "ok" && data.database === "connected") {
        setMessage(data.message);
        setStatus("success");
        setUtilisateurs(data.result);
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

        <button type="button" onClick={getUtilisateurs} disabled={loading}>
          {loading ? "Récupération..." : "Récupérer les utilisateurs"}
        </button>

        {message && (
          <p role="status" className={`connection-message ${status}`}>
            {message}
          </p>
        )}

        {utilisateurs.length > 0 && (
          <div>
            <h3>Liste des utilisateurs :</h3>
            <ul>
              {utilisateurs.map((utilisateur) => (
                <li key={utilisateur.id}>
                  {utilisateur.nom} - {utilisateur.email}
                </li>
              ))}
            </ul>
          </div>
        )}
      </section>
      <InscriptionForm/>
    </main>
  );
}

export default Accueil;
