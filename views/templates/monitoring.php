<?php // CLARISSE
    /** 
     * Affichage de la partie monitoring : liste des articles avec la date, nombre de vues, de commentaires et un bouton "gérer les commentaires" pour chacun. 
     * Et un formulaire pour ajouter un article. 
     */
?>

<h2>Monitoring des articles</h2>

<?php
    // Current sorting
    $currentSort = $sort ?? null;
    $currentOrder = $order ?? 'asc';

    // Helper to build sort links (toggle asc/desc)
    function sort_link($field, $label, $currentSort, $currentOrder) {
        $order = 'asc';
        if ($currentSort === $field && $currentOrder === 'asc') {
            $order = 'desc';
        }
        $arrow = '';
        if ($currentSort === $field) {
            $arrow = $currentOrder === 'asc' ? ' ▲' : ' ▼';
        }
        return '<a class="sortLink" href="index.php?action=monitoring&sort=' . $field . '&order=' . $order . '">' . $label . $arrow . '</a>';
    }
?>

<div class="adminArticle">
    <table class="commentsTable">
        <colgroup>
            <col style="width:200px;" /> <!-- title -->
            <col style="width:250px;" /> <!-- date -->
            <col style="width:150px;" /> <!-- vues -->
            <col style="width:150px;" /> <!-- commentaires -->
            <col /> <!-- actions (auto) -->
        </colgroup>
        <thead>
            <tr>
                <th><?= sort_link('title', 'Titre', $currentSort, $currentOrder) ?></th>
                <th><?= sort_link('date', 'Date', $currentSort, $currentOrder) ?></th>
                <th><?= sort_link('vues', 'Vues', $currentSort, $currentOrder) ?></th>
                <th><?= sort_link('comments', 'Commentaires', $currentSort, $currentOrder) ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article) : ?>
                <?php $date = $article->getDateCreation(); ?>
                <tr>
                    <td><?= htmlspecialchars($article->getTitle()) ?></td>
                    <td><?php if ($date instanceof DateTime) { echo $date->format('d/m/Y'); } else { echo htmlspecialchars((string)$date); } ?></td>
                    <td><?= (int)$article->getVues() ?> vues</td>
                    <td><?= (int)$commentManager->getNumberCommentsByArticleId($article->getId()) ?> commentaires</td>
                    <td class="commentActions"><a class="submit" href="index.php?action=showManageComments&id=<?= $article->getId() ?>">Gérer les commentaires</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>