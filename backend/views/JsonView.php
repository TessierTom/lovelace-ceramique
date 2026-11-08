if (data.status === "ok") {
    setMessage(data.message);
    setCommandes(data.data);  // ← "data" au lieu de "result"
}

{
  "status":   "ok",
  "database": "connected",
  "message":  "Commande créée avec succès",
  "result":   { "id": 7, "nom_client": "Marie", "date_creation": "2026-06-24", "statut": "a_faire" }
}