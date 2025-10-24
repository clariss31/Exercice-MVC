<?php 
/**
 * Contrôleur de la partie admin.
 */
 
class AdminController {

    /**
     * Affiche la page d'administration.
     * @return void
     */
    public function showAdmin() : void
    {
        // On vérifie que l'utilisateur est connecté.
        $this->checkIfUserIsConnected();

        // On récupère les articles.
        $articleManager = new ArticleManager();
        $articles = $articleManager->getAllArticles();

        // On affiche la page d'administration.
        $view = new View("Administration");
        $view->render("admin", [
            'articles' => $articles
        ]);
    }

    /**
     * Vérifie que l'utilisateur est connecté.
     * @return void
     */
    private function checkIfUserIsConnected() : void
    {
        // On vérifie que l'utilisateur est connecté.
        if (!isset($_SESSION['user'])) {
            Utils::redirect("connectionForm");
        }
    }

    /**
     * Affichage du formulaire de connexion.
     * @return void
     */
    public function displayConnectionForm() : void 
    {
        $view = new View("Connexion");
        $view->render("connectionForm");
    }

    /**
     * Connexion de l'utilisateur.
     * @return void
     */
    public function connectUser() : void 
    {
        // On récupère les données du formulaire.
        $login = Utils::request("login");
        $password = Utils::request("password");

        // On vérifie que les données sont valides.
        if (empty($login) || empty($password)) {
            throw new Exception("Tous les champs sont obligatoires. 1");
        }

        // On vérifie que l'utilisateur existe.
        $userManager = new UserManager();
        $user = $userManager->getUserByLogin($login);
        if (!$user) {
            throw new Exception("L'utilisateur demandé n'existe pas.");
        }

        // On vérifie que le mot de passe est correct.
        if (!password_verify($password, $user->getPassword())) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            throw new Exception("Le mot de passe est incorrect : $hash");
        }

        // On connecte l'utilisateur.
        $_SESSION['user'] = $user;
        $_SESSION['idUser'] = $user->getId();

        // On redirige vers la page d'administration.
        Utils::redirect("admin");
    }

    /**
     * Déconnexion de l'utilisateur.
     * @return void
     */
    public function disconnectUser() : void 
    {
        // On déconnecte l'utilisateur.
        unset($_SESSION['user']);

        // On redirige vers la page d'accueil.
        Utils::redirect("home");
    }

    /**
     * Affichage du formulaire d'ajout d'un article.
     * @return void
     */
    public function showUpdateArticleForm() : void 
    {
        $this->checkIfUserIsConnected();

        // On récupère l'id de l'article s'il existe.
        $id = Utils::request("id", -1);

        // On récupère l'article associé.
        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($id);

        // Si l'article n'existe pas, on en crée un vide. 
        if (!$article) {
            $article = new Article();
        }

        // On affiche la page de modification de l'article.
        $view = new View("Edition d'un article");
        $view->render("updateArticleForm", [
            'article' => $article
        ]);
    }

    /**
     * Ajout et modification d'un article. 
     * On sait si un article est ajouté car l'id vaut -1.
     * @return void
     */
    public function updateArticle() : void 
    {
        $this->checkIfUserIsConnected();

        // On récupère les données du formulaire.
        $id = Utils::request("id", -1);
        $title = Utils::request("title");
        $content = Utils::request("content");

        // On vérifie que les données sont valides.
        if (empty($title) || empty($content)) {
            throw new Exception("Tous les champs sont obligatoires. 2");
        }

        // On crée l'objet Article.
        $article = new Article([
            'id' => $id, // Si l'id vaut -1, l'article sera ajouté. Sinon, il sera modifié.
            'title' => $title,
            'content' => $content,
            'id_user' => $_SESSION['idUser']
        ]);

        // On ajoute l'article.
        $articleManager = new ArticleManager();
        $articleManager->addOrUpdateArticle($article);

        // On redirige vers la page d'administration.
        Utils::redirect("admin");
    }


    /**
     * Suppression d'un article.
     * @return void
     */
    public function deleteArticle() : void
    {
        $this->checkIfUserIsConnected();

        $id = Utils::request("id", -1);

        // On supprime l'article.
        $articleManager = new ArticleManager();
        $articleManager->deleteArticle($id);
       
        // On redirige vers la page d'administration.
        Utils::redirect("admin");
    }

    /**
     * Affichage de la page de gestion des commentaires.
     * @return void
     */
    public function showManageComments() : void
    {
        $this->checkIfUserIsConnected();

        // On récupère l'id de l'article.
        $id = Utils::request("id", -1);

        // On récupère les commentaires associés.
        $commentManager = new CommentManager();
        $comments = $commentManager->getAllCommentsByArticleId($id);

        // CLARISSE BEGIN
        // On récupère l'article associé pour afficher son titre
        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($id);

        // On affiche la page de gestion des commentaires.
        $view = new View("Gestion des commentaires");
        $view->render("manageComments", [
            'comments' => $comments,
            'articleId' => $id,
            'article' => $article
        ]);
    }

    /**
     * Supprime un commentaire unique (action POST).
     * @return void
     */
    public function deleteComment() : void
    {
        $this->checkIfUserIsConnected();

        $id = Utils::request('id', -1);
        if ($id <= 0) {
            throw new Exception('Identifiant de commentaire invalide.');
        }

        $commentManager = new CommentManager();
        $comment = $commentManager->getCommentById($id);
        if (!$comment) {
            throw new Exception('Commentaire introuvable.');
        }

        $success = $commentManager->deleteComment($comment);
        // On redirige vers la gestion des commentaires du même article
        Utils::redirect('showManageComments', ['id' => $comment->getIdArticle()]);
    }

    /**
     * Supprime plusieurs commentaires sélectionnés (action POST).
     * @return void
     */
    public function deleteComments() : void
    {
        $this->checkIfUserIsConnected();

        $selected = Utils::request('selected', []);
        $articleId = Utils::request('articleId', -1);

        if (empty($selected) || !is_array($selected)) {
            throw new Exception('Aucun commentaire sélectionné.');
        }

        $commentManager = new CommentManager();
        foreach ($selected as $commentId) {
            $comment = $commentManager->getCommentById((int)$commentId);
            if ($comment) {
                $commentManager->deleteComment($comment);
            }
        }

        Utils::redirect('showManageComments', ['id' => $articleId]);
    }

    /**
     * Affichage de la page de monitoring (liste des articles + stats).
     * @return void
     */
    public function showMonitoring() : void
    {
        $this->checkIfUserIsConnected();

        // On récupère les articles.
        $articleManager = new ArticleManager();
        $articles = $articleManager->getAllArticles();

        // Récupérer critères de tri depuis la requête
        $sort = Utils::request('sort', null); // 'title'|'date'|'vues'|'comments'
        $order = Utils::request('order', 'asc'); // 'asc'|'desc'

        // Pré-calculer le nombre de commentaires par article pour tri si nécessaire
        $commentManager = new CommentManager();
        $commentCounts = [];
        foreach ($articles as $a) {
            $commentCounts[$a->getId()] = $commentManager->getNumberCommentsByArticleId($a->getId());
        }

        // Tri en PHP selon le critère demandé
        if ($sort) {
            usort($articles, function($a, $b) use ($sort, $order, $commentCounts) {
                $dir = ($order === 'asc') ? 1 : -1;
                switch ($sort) {
                    case 'title':
                        return $dir * strcmp(mb_strtolower($a->getTitle()), mb_strtolower($b->getTitle()));
                    case 'date':
                        $da = $a->getDateCreation();
                        $db = $b->getDateCreation();
                        $ta = ($da instanceof DateTime) ? $da->getTimestamp() : 0;
                        $tb = ($db instanceof DateTime) ? $db->getTimestamp() : 0;
                        return $dir * ($ta <=> $tb);
                    case 'vues':
                        $va = (int) $a->getVues();
                        $vb = (int) $b->getVues();
                        return $dir * ($va <=> $vb);
                    case 'comments':
                        $ca = $commentCounts[$a->getId()] ?? 0;
                        $cb = $commentCounts[$b->getId()] ?? 0;
                        return $dir * ($ca <=> $cb);
                    default:
                        return 0;
                }
            });
        }

        // On affiche la page de monitoring.
        $view = new View("Monitoring");
        $view->render("monitoring", [
            'articles' => $articles,
            'commentManager' => $commentManager,
            'sort' => $sort,
            'order' => $order
        ]);
    }
    // CLARISSE END
}