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

  // === AJOUT : GET /api/commande — liste les commandes ===
  async function getCommandes() {
    setLoading(true);
    setMessage("");
    setStatus("");

    try {
      const response = await fetch("/api/commande", {
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

  // === AJOUT : POST /api/commande — crée une commande ===
  async function creerCommande(e) {
    e.preventDefault();
    setLoading(true);
    setMessage("");
    setStatus("");

    try {
      const response = await fetch("/api/commande", {
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