import { useState } from "react";
import "./InscriptionForm.css";

const champsInitiaux = {
  organisation_nom: "",
  nom: "",
  identifiant: "",
  email: "",
  mdp: "",
};

function InscriptionForm() {
  const [champs, setChamps] = useState(champsInitiaux);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");
  const [status, setStatus] = useState("");
  const [erreurs, setErreurs] = useState([]);

  function modifierChamp(event) {
    const { name, value } = event.target;
    setChamps((precedent) => ({ ...precedent, [name]: value }));
  }

  async function soumettre(event) {
    event.preventDefault();
    setLoading(true);
    setMessage("");
    setStatus("");
    setErreurs([]);

    try {
      const response = await fetch("/api/utilisateurs", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(champs),
      });

      const data = await response.json();

      if (!response.ok) {
        setErreurs(Array.isArray(data.erreurs) ? data.erreurs : []);
        throw new Error(data.message || "Création impossible");
      }

      setMessage(
        `Compte créé : ${data.utilisateur.identifiant} (admin) pour « ${data.organisation.nom} ».`,
      );
      setStatus("success");
      setChamps(champsInitiaux);
    } catch (error) {
      setMessage(error.message);
      setStatus("error");
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="inscription">
      <h2>Créer une organisation</h2>
      <p>Créez votre organisation et le compte administrateur associé.</p>

      <form onSubmit={soumettre}>
        <label>
          Nom de l&apos;organisation
          <input
            type="text"
            name="organisation_nom"
            value={champs.organisation_nom}
            onChange={modifierChamp}
            maxLength={50}
            required
            autoComplete="organization"
          />
        </label>

        <label>
          Nom de l&apos;administrateur
          <input
            type="text"
            name="nom"
            value={champs.nom}
            onChange={modifierChamp}
            maxLength={50}
            required
            autoComplete="name"
          />
        </label>

        <label>
          Identifiant
          <input
            type="text"
            name="identifiant"
            value={champs.identifiant}
            onChange={modifierChamp}
            maxLength={25}
            required
            autoComplete="username"
          />
        </label>

        <label>
          Email
          <input
            type="email"
            name="email"
            value={champs.email}
            onChange={modifierChamp}
            maxLength={120}
            required
            autoComplete="email"
          />
        </label>

        <label>
          Mot de passe
          <input
            type="password"
            name="mdp"
            value={champs.mdp}
            onChange={modifierChamp}
            minLength={8}
            required
            autoComplete="new-password"
          />
        </label>

        <button type="submit" disabled={loading}>
          {loading ? "Création..." : "Créer le compte admin"}
        </button>
      </form>

      {message && (
        <p role="status" className={`connection-message ${status}`}>
          {message}
        </p>
      )}

      {erreurs.length > 0 && (
        <ul className="erreurs-liste">
          {erreurs.map((erreur) => (
            <li key={erreur}>{erreur}</li>
          ))}
        </ul>
      )}
    </section>
  );
}

export default InscriptionForm;