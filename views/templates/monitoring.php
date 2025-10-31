<?php
    // CLARISSE
    /** * Affichage de la partie monitoring : liste des articles avec la date, nombre de vues, de commentaires et un bouton "gérer les commentaires" pour chacun. 
     * Et un formulaire pour ajouter un article. 
     */
?>

<h2>Monitoring des articles</h2>

<?php
    // Préparer les variables pour les liens de tri
    $currentSort = $sort ?? null;
    $currentOrder = $order ?? 'asc';

    // Gestion du tri avec flèches et labels
    function sort_link($field, $label, $currentSort, $currentOrder) {
        $order = 'asc';
        $arrow = ' ▼';
        $arrowClass = 'sortArrow';

        if ($currentSort === $field) {
            $arrowClass .= ' active';
            if ($currentOrder === 'asc') {
                $order = 'desc';
                $arrow = ' ▲'; // Active, ascending
            } else {
                $arrow = ' ▼'; // Active, descending
            }
        }
        
        // Construction du lien avec le label et la flèche
        $link = '<a class="sortLink" href="index.php?action=monitoring&sort=' . $field . '&order=' . $order . '">' 
                . $label . '<span class="' . $arrowClass . '">' . $arrow . '</span></a>';
        
        // Label (croissant/décroissant)
        if ($currentSort === $field) {
            $orderLabel = $currentOrder === 'asc' ? 'croissant' : 'décroissant';
            $link .= '<span class="sortOrder"> ' . $orderLabel . '</span>';
        }
        
        return $link;
    }
?>

<div class="adminArticle">
    <table class="commentsTable">
        <colgroup class="colgroup">
            <col class="col-title" />
            <col class="col-date" />
            <col class="col-views" />
            <col class="col-comments" />
            <col class="col-actions" />
        </colgroup>
        <thead>
            <tr>
                <th><?= sort_link('title', 'Titre', $currentSort, $currentOrder) ?></th>
                <th><?= sort_link('date', 'Date', $currentSort, $currentOrder) ?></th>
                <th><?= sort_link('views', 'Vues', $currentSort, $currentOrder) ?></th>
                <th><?= sort_link('comments', 'Commentaires', $currentSort, $currentOrder) ?></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article) : ?>
                <?php $date = $article->getDateCreation(); ?>
                <tr>
                    <td><?= htmlspecialchars($article->getTitle()) ?></td>
                    <td><?php if ($date instanceof DateTime) { echo $date->format('d/m/Y'); } else { echo htmlspecialchars((string)$date); } ?></td>
                    <td><?= (int)$article->getViews() ?> vues</td>
                    <td><?= (int)$commentManager->getNumberCommentsByArticleId($article->getId()) ?> commentaires</td>
                    <td class="commentActions"><a class="submit" href="index.php?action=showManageComments&id=<?= $article->getId() ?>">Gérer les commentaires</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>