import { useEffect, useState } from "react";
import "./Kanban.css";

// Page d'accueil (US.14) : une colonne par étape de l'atelier, une carte par pièce à fabriquer.
// Sur chaque carte, un bouton (US.12) passe la pièce à l'étape suivante.
function Kanban() {
  const [etapes, setEtapes] = useState([]); // les colonnes, dans l'ordre de l'atelier
  const [tickets, setTickets] = useState([]); // les cartes (une pièce = un ticket)
  const [loading, setLoading] = useState(true); // true tant que la première réponse n'est pas arrivée
  const [message, setMessage] = useState(""); // texte affiché à l'utilisateur
  const [status, setStatus] = useState(""); // "success" ou "error" : choisit la couleur du message
  const [enCours, setEnCours] = useState(null); // id de la carte en train de changer d'étape (bloque le double clic)

  // useEffect avec [] : lancé une seule fois, quand la page s'affiche
  useEffect(() => {
    async function chargerKanban() {
      try {
        const response = await fetch("/api/tickets"); // GET par défaut : on lit
        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || "Chargement impossible");
        }

        setEtapes(data.etapes);
        setTickets(data.tickets);
      } catch (error) {
        setMessage(error.message);
        setStatus("error");
      } finally {
        setLoading(false);
      }
    }

    chargerKanban();
  }, []);

  // US.12 : demande au back de passer la pièce à l'étape suivante
  async function passerEtapeSuivante(ticketId) {
    setEnCours(ticketId);
    setMessage("");

    try {
      // PATCH = modifier une partie d'une donnée existante (seulement l'étape du ticket)
      const response = await fetch(`/api/tickets/${ticketId}/etape-suivante`, {
        method: "PATCH",
      });
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Changement d'étape impossible");
      }

      // Mise à jour locale : la carte change de colonne sans recharger toute la page
      setTickets((prev) =>
        prev.map((ticket) =>
          ticket.id === ticketId ? { ...ticket, etape_id: data.etape.id } : ticket,
        ),
      );
      setMessage(data.message);
      setStatus("success");
    } catch (error) {
      setMessage(error.message);
      setStatus("error");
    } finally {
      setEnCours(null);
    }
  }

  if (loading) {
    return <p>Chargement de l&apos;atelier…</p>;
  }

  // La dernière colonne (Terminé) n'a pas de bouton : il n'y a pas d'étape après
  const derniereEtape = etapes[etapes.length - 1];

  return (
    <section className="kanban-section">
      <h2>Commandes à faire</h2>

      {message && (
        <p role="status" className={`connection-message ${status}`}>
          {message}
        </p>
      )}

      <div className="kanban">
        {etapes.map((etape) => {
          // Les cartes de cette colonne : les tickets qui sont à cette étape
          const cartes = tickets.filter((ticket) => ticket.etape_id === etape.id);

          return (
            <div key={etape.id} className="kanban-colonne">
              <h3>
                {etape.nom}
                <span className="kanban-compteur">{cartes.length}</span>
              </h3>

              {cartes.map((ticket) => (
                <article key={ticket.id} className="kanban-carte">
                  <p className="kanban-piece">{ticket.piece}</p>
                  <p>
                    Commande n°{ticket.commande_id} · {ticket.nom_client}
                  </p>
                  {/* ?? : si personne n'est assigné (null), on l'écrit clairement */}
                  <p className="kanban-artisan">{ticket.artisan ?? "Non assignée"}</p>

                  {etape.id !== derniereEtape.id && (
                    <button
                      type="button"
                      onClick={() => passerEtapeSuivante(ticket.id)}
                      disabled={enCours === ticket.id}
                    >
                      {enCours === ticket.id ? "…" : "Étape suivante →"}
                    </button>
                  )}
                </article>
              ))}
            </div>
          );
        })}
      </div>
    </section>
  );
}

export default Kanban;