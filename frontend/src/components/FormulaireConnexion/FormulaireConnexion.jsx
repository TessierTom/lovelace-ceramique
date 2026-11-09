import { useId, useState } from "react";
import { useNavigate } from "react-router-dom";
import "./FormulaireConnexion.css";

/**
 * Formulaire d'authentification.
 * Envoie un POST JSON { email, motDePasse } vers /api/connexion.
 *
 * Props :
 *  - onSuccess(data) : appelé avec la réponse JSON quand la connexion réussit
 *  - endpoint        : URL de l'API (par défaut "/api/connexion")
 */
function FormulaireConnexion() {
  const uid = useId();
  const idIdentifiant = `${uid}-identifiant`;
  const idMdp = `${uid}-mdp`;
  const idErreur = `${uid}-erreur`;
  const redirectTo = "/"; // URL de redirection après connexion réussie
  const CLE_TOKEN = "token";
  const CLE_UTILISATEUR = "utilisateur";

  const navigate = useNavigate();

  const [identifiant, setIdentifiant] = useState("");
  const [motDePasse, setMotDePasse] = useState("");
  const [chargement, setChargement] = useState(false);
  const [erreur, setErreur] = useState("");

  async function handleSubmit(e) {
    e.preventDefault();
    if (chargement) return;

    setErreur("");
    setChargement(true);

    try {
      const reponse = await fetch("/api/connexion", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        credentials: "include", // nécessaire si le serveur pose un cookie de session
        body: JSON.stringify({ identifiant: identifiant.trim(), mdp: motDePasse }),
      });

      let data = null;
      try {
        data = await reponse.json();
      } catch {
        // réponse sans corps JSON : on ignore
      }

      if (!reponse.ok) {
        if (reponse.status === 400 || reponse.status === 401) {
          setErreur(
            data?.message ||
              "Adresse e-mail ou mot de passe incorrect. Vérifiez vos informations et réessayez."
          );
        } else if (reponse.status === 429) {
          setErreur(
            "Trop de tentatives. Patientez quelques minutes avant de réessayer."
          );
        } else {
          setErreur(
            data?.message ||
              "Le serveur a rencontré un problème. Réessayez dans un instant."
          );
        }
        return;
      }

      if (data?.status !== "ok" || !data?.token || !data?.utilisateur) {
        setErreur(
          data?.message || "Réponse inattendue du serveur. Réessayez dans un instant."
        );
        return;
      }
 
      try {
        localStorage.setItem(CLE_TOKEN, data.token);
        localStorage.setItem(CLE_UTILISATEUR, JSON.stringify(data.utilisateur));
      } catch (e) {
        console.log("Erreur token : ",e);
        setErreur(
          "Impossible d’enregistrer la session : le stockage du navigateur est désactivé ou saturé."
        );
        return;
      }
 
      const { token, utilisateur } = data;
      
      navigate("/", { state: { utilisateur } });
    } catch {
      setErreur(
        "Impossible de joindre le serveur. Vérifiez votre connexion et réessayez."
      );
    } finally {
      setChargement(false);
    }
  }

  return (
    <>
      <form className="fc-form" onSubmit={handleSubmit} aria-labelledby={`${uid}-titre`}>
        <h1 id={`${uid}-titre`} className="fc-titre">
          Connexion
        </h1>
        <p className="fc-sous-titre">Accédez à votre espace avec votre compte.</p>

        {erreur && (
          <div id={idErreur} className="fc-erreur" role="alert">
            {erreur}
          </div>
        )}

        <div className="fc-champ">
          <label htmlFor={idIdentifiant}>Identifiant</label>
          <input
            id={idIdentifiant}
            type="text"
            name="identifiant"
            autoComplete="username"
            inputMode="identifiant"
            required
            value={identifiant}
            onChange={(e) => setIdentifiant(e.target.value)}
            aria-invalid={erreur ? "true" : undefined}
            aria-describedby={erreur ? idErreur : undefined}
            disabled={chargement}
          />
        </div>

        <div className="fc-champ">
          <label htmlFor={idMdp}>Mot de passe</label>
          <div className="fc-mdp">
            <input
              id={idMdp}
              type="password"
              name="motDePasse"
              autoComplete="current-password"
              required
              value={motDePasse}
              onChange={(e) => setMotDePasse(e.target.value)}
              aria-invalid={erreur ? "true" : undefined}
              aria-describedby={erreur ? idErreur : undefined}
              disabled={chargement}
            />
          </div>
        </div>

        <button type="submit" className="fc-envoyer" disabled={chargement}>
          {chargement ? "Connexion en cours…" : "Se connecter"}
        </button>
      </form>
    </>
  );
}

export function getToken() {
  try {
    return localStorage.getItem("token");
  } catch {
    return null;
  }
}

export default FormulaireConnexion;