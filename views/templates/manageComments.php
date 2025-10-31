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
        <form id="bulkDeleteForm" method="post" action="index.php?action=deleteComments" class="adminArticle manageCommentsContainer">
            <input type="hidden" name="articleId" value="<?= (int)$articleId ?>" />

            <table class="commentsTable">
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
                            <td class="commentActions">
                                <button type="button" class="submit singleDelete" data-id="<?= (int)$comment->getId() ?>" data-article="<?= (int)$articleId ?>">Supprimer</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="bulk-actions">
                <button type="submit" class="submit" id="bulkDeleteButton"> Supprimer la sélection </button>
            </div>
        </form>

        <script>
            (function() {
                // Gestion de la case "Tout sélectionner"
                const selectAll = document.getElementById('selectAll');
                if (selectAll) {
                    selectAll.addEventListener('change', e => {
                        document.querySelectorAll('.selectItem').forEach(cb => cb.checked = e.target.checked);
                    });
                }

                // Logique pour la suppression groupée
                const bulkForm = document.getElementById('bulkDeleteForm');
                const bulkButton = document.getElementById('bulkDeleteButton');
                
                if (bulkButton && bulkForm) {
                    bulkButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        const selectedCheckboxes = document.querySelectorAll('.selectItem:checked');
                        
                        if (selectedCheckboxes.length === 0) {
                            alert('Veuillez sélectionner un ou plusieurs commentaires');
                        } else {
                            if (confirm('Supprimer les commentaires sélectionnés ?')) {
                                bulkForm.submit();
                            }
                        }
                    });
                }

                // Bouton "Supprimer" pour la suppression simple
                document.querySelectorAll('.singleDelete').forEach(button => {
                    button.addEventListener('click', function() {
                        const deleteUrl = `index.php?action=deleteComment&id=${this.dataset.id}&article=${this.dataset.article}`;
                        
                        if (confirm('Supprimer ce commentaire ?')) {
                            window.location.href = deleteUrl;
                        }
                    });
                });
            })();
        </script>
    </div>
<?php } ?>