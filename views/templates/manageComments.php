<?php
/** CLARISSE
 * Gestion des commentaires d'un article.
 * Variables attendues :
 * - $comments : array of Comment
 * - $articleId : int
 * - $article : Article (optionnel)
 */
?>

<h2>Commentaires de l'article : <?= htmlspecialchars($article->getTitle()) ?></h2>

<?php if (empty($comments)) { ?>
    <p>Aucun commentaire pour cet article.</p>
<?php } else { ?>
    <div>
        <!-- AJOUT: Classe "adminArticle" pour appliquer le style du tableau
             AJOUT: Classe "manageCommentsContainer" pour les styles spécifiques à cette page -->
        <form id="bulkDeleteForm" method="post" action="index.php?action=deleteComments" class="adminArticle manageCommentsContainer">
            <input type="hidden" name="articleId" value="<?= (int)$articleId ?>" />

            <table class="commentsTable">
                <!-- MODIFIÉ: Remplacement des styles en ligne par des classes -->
                <colgroup>
                    <col class="col-check" />
                    <col class="col-author" />
                    <col class="col-date-comment" />
                    <col class="col-content" />
                    <col class="col-actions-comment" />
                </colgroup>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" /></th>
                        <th>Auteur</th>
                        <th>Date</th>
                        <th>Commentaire</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment) { ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected[]" value="<?= (int)$comment->getId() ?>" class="selectItem" />
                            </td>
                            
                            <td>
                                <!-- MODIFIÉ: Suppression de la balise <strong>, le style viendra du CSS -->
                                <?= htmlspecialchars($comment->getPseudo()) ?>
                            </td>
                            
                            <td>
                                <?php $d = $comment->getDateCreation();
                                if ($d instanceof DateTime) {
                                    echo $d->format('d/m/Y');
                                } else {
                                    echo htmlspecialchars((string)$d);
                                } ?>
                            </td>

                            <td><?= Utils::format($comment->getContent()) ?></td>
                            <td class="commentActions"> <!-- AJOUT: Classe pour centrer le bouton si besoin -->
                                <button type="button" class="submit singleDelete" data-id="<?= (int)$comment->getId() ?>" data-article="<?= (int)$articleId ?>">Supprimer</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- AJOUT: Classe pour espacer ce bloc du tableau -->
            <div class="bulk-actions">
                <!-- MODIFIÉ: Ajout d'un id et suppression du onclick -->
                <button type="submit" class="submit" id="bulkDeleteButton">
                    Supprimer la sélection
                </button>
            </div>
        </form>

        <!-- AJOUT: Modal pour les alertes et confirmations -->
        <div id="customModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
            <div style="background-color: #fefefe; margin: auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 400px; border-radius: 8px; text-align: center; color: #333; font-family: Arial, sans-serif;">
                <p id="customModalMessage" style="margin-bottom: 20px; font-size: 16px; line-height: 1.5;"></p>
                <div id="customModalButtons">
                    <button id="customModalConfirm" style="background-color: #255E33; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; min-width: 80px;">OK</button>
                    <button id="customModalCancel" style="background-color: #ccc; color: #333; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; min-width: 80px;">Annuler</button>
                </div>
            </div>
        </div>


        <script>
            // MODIFIÉ: Encapsulation de tout le script dans un IIFE
            (function() {
                // Références du Modal
                const modal = document.getElementById('customModal');
                const modalMessage = document.getElementById('customModalMessage');
                const modalConfirm = document.getElementById('customModalConfirm');
                const modalCancel = document.getElementById('customModalCancel');
                let confirmCallback = null;

                // Fonction pour afficher une alerte personnalisée
                function showCustomAlert(message) {
                    modalMessage.textContent = message;
                    modalConfirm.textContent = 'OK';
                    modalConfirm.style.display = 'inline-block';
                    modalCancel.style.display = 'none';
                    modal.style.display = 'flex';
                    
                    modalConfirm.onclick = function() {
                        modal.style.display = 'none';
                    }
                    modalCancel.onclick = null; // Nettoyer
                }

                // Fonction pour afficher une confirmation personnalisée
                function showCustomConfirm(message, callback) {
                    confirmCallback = callback;
                    modalMessage.textContent = message;
                    modalConfirm.textContent = 'Confirmer';
                    modalConfirm.style.display = 'inline-block';
                    modalCancel.style.display = 'inline-block';
                    modal.style.display = 'flex';

                    modalConfirm.onclick = function() {
                        modal.style.display = 'none';
                        if (confirmCallback) confirmCallback(true);
                    }
                    
                    modalCancel.onclick = function() {
                        modal.style.display = 'none';
                        if (confirmCallback) confirmCallback(false);
                    }
                }


                // Gestion de la case "Tout sélectionner"
                const selectAll = document.getElementById('selectAll');
                if (selectAll) {
                    selectAll.addEventListener('change', e => {
                        document.querySelectorAll('.selectItem').forEach(cb => cb.checked = e.target.checked);
                    });
                }

                // AJOUT: Logique pour la suppression groupée
                const bulkForm = document.getElementById('bulkDeleteForm');
                const bulkButton = document.getElementById('bulkDeleteButton');
                
                if (bulkButton && bulkForm) {
                    bulkButton.addEventListener('click', function(e) {
                        e.preventDefault(); // Toujours empêcher la soumission par défaut
                        
                        const selectedCheckboxes = document.querySelectorAll('.selectItem:checked');
                        
                        if (selectedCheckboxes.length === 0) {
                            showCustomAlert('Veuillez sélectionner un ou plusieurs commentaires');
                        } else {
                            showCustomConfirm('Supprimer les commentaires sélectionnés ?', function(isConfirmed) {
                                if (isConfirmed) {
                                    bulkForm.submit(); // Soumettre le formulaire si confirmé
                                }
                            });
                        }
                    });
                }


                // MODIFIÉ: Remplacement du confirm() natif pour la suppression simple
                document.querySelectorAll('.singleDelete').forEach(button => {
                    button.addEventListener('click', function() {
                        const deleteUrl = `index.php?action=deleteComment&id=${this.dataset.id}&article=${this.dataset.article}`;
                        showCustomConfirm('Supprimer ce commentaire ?', function(isConfirmed) {
                            if (isConfirmed) {
                                window.location.href = deleteUrl;
                            }
                        });
                    });
                });
            })();
        </script>
    </div>
<?php } ?>

