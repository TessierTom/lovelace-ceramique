<?php
/** @var array $pieces        [['id'=>1,'nom'=>'Vase tourné'], ...] */
/** @var int   $utilisateurId utilisateur connecté (créateur de la commande) */
$pieces = $pieces ?? [];
$utilisateurId = (int)($utilisateurId ?? 0);
?>
<section class="commande-page">
  <h2>Nouvelle commande</h2>
  <p class="page-desc">
    Chaque exemplaire commandé génère un ticket, créé à la première étape de fabrication.
  </p>

  <div id="form-errors" class="form-errors" role="alert" hidden></div>
  <p id="form-success" class="form-message success" role="status" hidden></p>

  <form id="commande-form" class="commande-form">
    <input type="hidden" id="utilisateur_id" value="<?= $utilisateurId ?>" />

    <div class="form-group">
      <label for="nom_client">Nom du client *</label>
      <input id="nom_client" name="nom_client" type="text" maxlength="150"
             placeholder="Ex : Galerie Terreau" required />
    </div>

    <hr class="form-divider" />

    <div id="lignes"></div>
    <button type="button" id="ajouter-ligne" class="btn">+ Ajouter une pièce</button>

    <div class="form-preview">
      📋 Générera <strong id="nb-tickets">0</strong> ticket(s)
    </div>

    <button type="submit" class="btn btn-primary btn-lg">🏺 Créer la commande</button>
  </form>
</section>

<script>
const PIECES = <?= json_encode($pieces, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
const form = document.getElementById('commande-form');
const lignes = document.getElementById('lignes');
const errBox = document.getElementById('form-errors');
const okBox = document.getElementById('form-success');

function ajouterLigne() {
  const row = document.createElement('div');
  row.className = 'form-row ligne';

  const select = document.createElement('select');
  select.className = 'piece';
  for (const p of PIECES) {
    const o = document.createElement('option');
    o.value = p.id;
    o.textContent = p.nom;
    select.appendChild(o);
  }

  const qte = document.createElement('input');
  qte.type = 'number'; qte.className = 'quantite';
  qte.min = 1; qte.max = 500; qte.value = 1;
  qte.addEventListener('input', majTotal);

  const suppr = document.createElement('button');
  suppr.type = 'button'; suppr.textContent = '✕';
  suppr.setAttribute('aria-label', 'Retirer cette ligne');
  suppr.addEventListener('click', () => { row.remove(); majTotal(); });

  row.append(select, qte, suppr);
  lignes.appendChild(row);
  majTotal();
}

function majTotal() {
  let total = 0;
  lignes.querySelectorAll('.quantite').forEach(q => total += parseInt(q.value, 10) || 0);
  document.getElementById('nb-tickets').textContent = total;
}

document.getElementById('ajouter-ligne').addEventListener('click', ajouterLigne);

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  errBox.hidden = okBox.hidden = true;

  const payload = {
    nom_client: document.getElementById('nom_client').value,
    utilisateur_id: parseInt(document.getElementById('utilisateur_id').value, 10),
    lignes: [...lignes.querySelectorAll('.ligne')].map(r => ({
      piece_id: parseInt(r.querySelector('.piece').value, 10),
      quantite: parseInt(r.querySelector('.quantite').value, 10),
    })),
  };

  try {
    const res = await fetch('/api/commandes', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const json = await res.json();
    if (!res.ok) {
      const msgs = (json.errors && json.errors.length) ? json.errors : [json.message];
      errBox.replaceChildren(...msgs.map(m => Object.assign(document.createElement('p'), { textContent: m })));
      errBox.hidden = false;
      return;
    }
    okBox.textContent = json.message;
    okBox.hidden = false;
    form.reset(); lignes.replaceChildren(); ajouterLigne();
  } catch {
    errBox.textContent = 'Erreur réseau, réessayez.';
    errBox.hidden = false;
  }
});

ajouterLigne();
</script>
