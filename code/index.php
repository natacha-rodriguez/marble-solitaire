<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Peg Solitaire solution</title>
        <?php
        include_once 'MarbleSolitaireGeneticSolution.php';
        include_once 'SolutionPersistenceManager.php';
        ?>
    </head>
    <body>
        <?php
        $solver = new MarbleSolitaireGeneticSolution();
        $solution = $solver->solve();
        $persistenceManager = SolutionPersistenceManager::getInstance();
        $success = $persistenceManager->insertSolution($solution);
        if ($success) {
            echo'<p> solution successfully saved</p>';
        } else {
            echo'<p> failed to save solution</p>';
        }

        $inverseSolution = array_reverse($solution);
        $success = $persistenceManager->insertSolution($inverseSolution);
        if ($success) {
            echo'<p> reverse solution successfully saved</p>';
        } else {
            echo'<p> failed to save solution</p>';
        }

        $firstSolution = $persistenceManager->getSolution('1');
        var_dump($firstSolution);

        unset($solution);
        unset($inverseSolution);
        unset($persistenceManager);
        unset($firstSolution);        
        ?>
    </body>
</html>