import { useState } from "react";
import "./RegisterForm.css";

const INITIAL_FORM = {
  organisation_nom: "",
  identifiant: "",
  email: "",
  nom: "",
  mdp: "",
  mdpConfirm: "",
};

function RegisterForm() {
  const [form, setForm] = useState(INITIAL_FORM);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");
  const [status, setStatus] = useState("");
  const [submitted, setSubmitted] = useState(false);

  function handleChange(event) {
    const { name, value } = event.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setLoading(true);
    setMessage("");
    setStatus("");

    if (form.mdp !== form.mdpConfirm) {
      setMessage("Les mots de passe ne correspondent pas");
      setStatus("error");
      setLoading(false);
      return;
    }

    if (form.mdp.length < 8) {
      setMessage("Le mot de passe doit contenir au moins 8 caractères");
      setStatus("error");
      setLoading(false);
      return;
    }

    try {
      const response = await fetch("/api/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          organisation_nom: form.organisation_nom.trim(),
          identifiant: form.identifiant.trim(),
          email: form.email.trim(),
          nom: form.nom.trim(),
          mdp: form.mdp,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Inscription impossible");
      }

      setMessage(
        `${data.message} (organisation n°${data.recu.organisation_nom}, compte ${data.recu.nom})`,
      );
      setStatus("success");
      setSubmitted(true);
      setForm(INITIAL_FORM);
    } catch (error) {
      setMessage(error.message);
      setStatus("error");
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="register-section">
      <h2>Créer mon organisation</h2>

      {!submitted && (
        <>
          <p>
            Créez votre organisation et votre compte{" "}
            <strong>Administrateur</strong>. Le mot de passe est stocké
            uniquement sous forme de hash (<code>password_hash</code>).
          </p>

          <form className="register-form" onSubmit={handleSubmit}>
            <label>
              Nom de l&apos;organisation
              <input
                type="text"
                name="organisation_nom"
                value={form.organisation_nom}
                onChange={handleChange}
                maxLength={50}
                required
                autoComplete="organization"
              />
            </label>

            <label>
              Identifiant
              <input
                type="text"
                name="identifiant"
                value={form.identifiant}
                onChange={handleChange}
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
                value={form.email}
                onChange={handleChange}
                maxLength={120}
                required
                autoComplete="email"
              />
            </label>

            <label>
              Nom
              <input
                type="text"
                name="nom"
                value={form.nom}
                onChange={handleChange}
                maxLength={50}
                required
                autoComplete="name"
              />
            </label>

            <label>
              Mot de passe
              <input
                type="password"
                name="mdp"
                value={form.mdp}
                onChange={handleChange}
                minLength={8}
                required
                autoComplete="new-password"
              />
            </label>

            <label>
              Confirmer le mot de passe
              <input
                type="password"
                name="mdpConfirm"
                value={form.mdpConfirm}
                onChange={handleChange}
                minLength={8}
                required
                autoComplete="new-password"
              />
            </label>

            <button type="submit" disabled={loading}>
              {loading ? "Création..." : "Créer mon organisation"}
            </button>
          </form>
        </>
      )}

      {message && (
        <p role="status" className={`connection-message ${status}`}>
          {message}
        </p>
      )}
    </section>
  );
}

export default RegisterForm;