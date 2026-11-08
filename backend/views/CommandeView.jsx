import { useState } from "react";

const TYPES = ["vase", "assiette", "bol", "tasse", "plat", "goblet", "autre"];

export default function CommandePage({ onCommandeCreee }) {
  const [form, setForm] = useState({
    nom: "",
    client: "",
    nom_piece: "",
    type_piece: "vase",
    nb_pieces: 4,
    description: "",
  });
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState(null);

  function handleChange(e) {
    const { name, value } = e.target;
    setForm((f) => ({ ...f, [name]: value }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setLoading(true);
    setMessage(null);

    try {
      const res = await fetch("/api/commandes", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          client: form.client || null,
          description: form.description || null,
          nom_piece: form.nom_piece,
          type_piece: form.type_piece,
          nb_pieces: parseInt(form.nb_pieces),
        }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message?.join(" ") || "Erreur de création");
      }

      setMessage({ type: "success", text: data.message });
      setForm({ nom: "", client: "", nom_piece: "", type_piece: "vase", nb_pieces: 4, description: "" });

      // Rafraîchit la page pièces
      onCommandeCreee?.();
    } catch (err) {
      setMessage({ type: "error", text: err.message });
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="commande-page">
      <h2>Nouvelle commande</h2>
      <p className="page-desc">
        Créez une commande : elle génère automatiquement des tickets pièces qui
        passeront par les 6 étapes de fabrication.
      </p>

      <form onSubmit={handleSubmit} className="commande-form">
        <div className="form-group">
          <label htmlFor="nom">Nom de la commande *</label>
          <input
            id="nom"
            name="nom"
            type="text"
            placeholder="Ex: Collection été 2026 – Atelier Sud"
            value={form.nom}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label htmlFor="client">Client (optionnel)</label>
          <input
            id="client"
            name="client"
            type="text"
            placeholder="Ex: Galerie Terreau"
            value={form.client}
            onChange={handleChange}
          />
        </div>6

        <hr className="form-divider" />

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="nom_piece">Nom de la pièce *</label>
            <input
              id="nom_piece"
              name="nom_piece"
              type="text"
              placeholder="Ex: Vase tourné"
              value={form.nom_piece}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="type_piece">Type</label>
            <select id="type_piece" name="type_piece" value={form.type_piece} onChange={handleChange}>
              {TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
            </select>
          </div>

          <div className="form-group">
            <label htmlFor="nb_pieces">Quantité</label>
            <input
              id="nb_pieces"
              name="nb_pieces"
              type="number"
              min="1"
              max="500"
              value={form.nb_pieces}
              onChange={handleChange}
              required
            />
          </div>
        </div>

        <div className="form-group">
          <label htmlFor="description">Description / notes</label>
          <textarea
            id="description"
            name="description"
            rows={3}
            placeholder="Glaze souhaitée, dimensions, particularités…"
            value={form.description}
            onChange={handleChange}
          />
        </div>

        <div className="form-preview">
          📋 Générera <strong>{form.nb_pieces || 0}</strong> ticket(s) :
          {form.nom_piece && ` "${form.nom_piece} #1" → "${form.nom_piece} #${form.nb_pieces}"`}
        </div>

        <button type="submit" className="btn btn-primary btn-lg" disabled={loading}>
          {loading ? "Création…" : "🏺 Créer la commande"}
        </button>

        {message && (
          <p className={`form-message ${message.type}`} role="status">
            {message.text}
          </p>
        )}
      </form>
    </section>
  );
}