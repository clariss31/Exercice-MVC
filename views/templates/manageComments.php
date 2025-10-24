<?php
/** CLARISSE
 * Gestion des commentaires d'un article.
 * Variables attendues :
 * - $comments : array of Comment
 * - $articleId : int
 * - $article : Article (optionnel)
 */
?>

<h2>Gestion des commentaires de l'article : <?= htmlspecialchars($article->getTitle()) ?></h2>

<?php if (empty($comments)) { ?>
    <p>Aucun commentaire pour cet article.</p>
<?php } else { ?>
    <div>
        <form id="bulkDeleteForm" method="post" action="index.php?action=deleteComments">
            <input type="hidden" name="articleId" value="<?= (int)$articleId ?>" />

            <table class="commentsTable">
                <colgroup>
                    <col style="width:40px" />
                    <col style="width:160px" />
                    <col />
                    <col style="width:160px" />
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
                                <strong><?= htmlspecialchars($comment->getPseudo()) ?></strong>
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
                            <td>
                                <button type="button" class="submit singleDelete" data-id="<?= (int)$comment->getId() ?>" data-article="<?= (int)$articleId ?>">Supprimer</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div>
                <button type="submit" class="submit" onclick="return confirm('Supprimer les commentaires sélectionnés ?')">
                    Supprimer la sélection
                </button>
            </div>
        </form>

        <script>
            // Gestion des sélections et suppression
            (function() {
                const selectAll = document.getElementById('selectAll');
                if (selectAll) {
                    selectAll.addEventListener('change', e => {
                        document.querySelectorAll('.selectItem').forEach(cb => cb.checked = e.target.checked);
                    });
                }

                document.querySelectorAll('.singleDelete').forEach(button => {
                    button.addEventListener('click', function() {
                        if (confirm('Supprimer ce commentaire ?')) {
                            // Redirection ou appel AJAX à définir selon ta logique serveur
                            window.location.href = `index.php?action=deleteComment&id=${this.dataset.id}&article=${this.dataset.article}`;
                        }
                    });
                });
            })();
        </script>
    </div>
<?php } ?>
