import { useState } from "react";
import "./App.css";
import InscriptionForm from "../components/InscriptionForm/InscriptionForm";

function App() {
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);
  const [status, setStatus] = useState("");
  const [utilisateurs, setUtilisateurs] = useState([]);

  // === AJOUT : état commandes + formulaire ===
  const [commandes, setCommandes] = useState([]);
  const [showForm, setShowForm] = useState(false);
  const [nomClient, setNomClient] = useState("");
  const [utilisateurId, setUtilisateurId] = useState("");
  // === FIN AJOUT ===

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

  // === AJOUT : GET /api/commandes — liste les commandes ===
  async function getCommandes() {
    setLoading(true);
    setMessage("");
    setStatus("");

    try {
      const response = await fetch("/api/commandes", {
        method: "GET",
        headers: {
          "Content-Type": "application/json"
        }
      });

      if (!response.ok) {
        throw new Error("Erreur lors de la récupération des commandes");
      }

      const data = await response.json();

      if (data.status === "ok" && data.database === "connected") {
        setMessage(data.message);
        setStatus("success");
        setCommandes(data.result);
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
  // === FIN AJOUT ===

  // === AJOUT : POST /api/commandes — crée une commande ===
  async function creerCommande(e) {
    e.preventDefault();
    setLoading(true);
    setMessage("");
    setStatus("");

    try {
      const response = await fetch("/api/commandes", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          nom_client: nomClient,
          utilisateur_id: parseInt(utilisateurId, 10),
        }),
      });

      if (!response.ok) {
        const err = await response.json();
        throw new Error(err.message || "Erreur lors de la création de la commande");
      }

      const data = await response.json();

      if (data.status === "ok" && data.database === "connected") {
        setMessage(data.message);
        setStatus("success");
        // Réinitialise le formulaire
        setNomClient("");
        setUtilisateurId("");
        setShowForm(false);
        // Recharge la liste
        await getCommandes();
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
  // === FIN AJOUT ===

  return (
    <main className="app">
      <h1>Lovelace Ceramique</h1>

      {/* Message de statut */}
      {message && (
        <p className={`status ${status}`}>{message}</p>
      )}

      {/* Vérifier connexion */}
      <button onClick={verifierConnexion} disabled={loading}>
        {loading ? "Vérification..." : "Vérifier la connexion"}
      </button>

      {/* Utilisateurs */}
      <button onClick={getUtilisateurs} disabled={loading}>
        {loading ? "Chargement..." : "Charger les utilisateurs"}
      </button>

      {utilisateurs.length > 0 && (
        <ul>
          {utilisateurs.map((u) => (
            <li key={u.id}>{u.nom || u.prenom} ({u.id})</li>
          ))}
        </ul>
      )}

      {/* === Commandes === */}
      <section>
        <h2>Commandes</h2>
        <button onClick={getCommandes} disabled={loading}>
          Charger les commandes
        </button>
        <button onClick={() => setShowForm(!showForm)} disabled={loading}>
          {showForm ? "Masquer" : "Nouvelle commande"}
        </button>

        {showForm && (
          <form onSubmit={creerCommande} className="form-commande">
            <label>
              Nom du client
              <input
                type="text"
                value={nomClient}
                onChange={(e) => setNomClient(e.target.value)}
                required
              />
            </label>
            <label>
              Utilisateur
              <select
                value={utilisateurId}
                onChange={(e) => setUtilisateurId(e.target.value)}
                required
              >
                <option value="">-- Choisir --</option>
                {utilisateurs.map((u) => (
                  <option key={u.id} value={u.id}>
                    {u.nom || u.prenom} (#{u.id})
                  </option>
                ))}
              </select>
            </label>
            <button type="submit" disabled={loading}>
              {loading ? "Création..." : "Créer la commande"}
            </button>
          </form>
        )}

        {commandes.length > 0 && (
          <ul className="liste-commandes">
            {commandes.map((c) => (
              <li key={c.id}>
                #{c.id} — {c.nom_client} — utilisateur #{c.utilisateur_id}
              </li>
            ))}
          </ul>
        )}
      </section>
    </main>
  );
}

export default App;