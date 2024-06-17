<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Replace with your GitHub personal access token
    $GITHUB_TOKEN = '';

    function getUserInfo($username, $GITHUB_TOKEN) {
        $url = "https://api.github.com/users/$username";
        $options = [
            'http' => [
                'header' => [
                    "User-Agent: PHP\r\n",
                    "Authorization: token $GITHUB_TOKEN\r\n"
                ]
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === FALSE) {
            $status_line = $http_response_header[0];
            preg_match('{HTTP/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];
            if ($status == 404) {
                return null;
            } else {
                throw new Exception("Failed to fetch user info: $status");
            }
        }

        return json_decode($response, true);
    }

    function getReposAndStars($username, $GITHUB_TOKEN) {
        $url = "https://api.github.com/users/$username/repos";
        $options = [
            'http' => [
                'header' => [
                    "User-Agent: PHP\r\n",
                    "Authorization: token $GITHUB_TOKEN\r\n"
                ]
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === FALSE) {
            $status_line = $http_response_header[0];
            preg_match('{HTTP/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];
            throw new Exception("Failed to fetch repositories: $status");
        }

        $repos = json_decode($response, true);
        $repo_count = count($repos);
        $total_stars = array_reduce($repos, function ($carry, $repo) {
            return $carry + $repo['stargazers_count'];
        }, 0);

        return ['repo_count' => $repo_count, 'total_stars' => $total_stars];
    }

    $username = $_POST['username'];

    try {
        $userInfo = getUserInfo($username, $GITHUB_TOKEN);
        if ($userInfo) {
            $jina = "User $username found!";
            $Followers = $userInfo['followers']."Followers";
            $Following = $userInfo['following']."Following";
            $repos_and_stars = getReposAndStars($username, $GITHUB_TOKEN);
            $repo = "Total Repository is ".$repos_and_stars['repo_count'];
            $star = "Total Star is ".$repos_and_stars['total_stars'];
        } else {
            $error = "User $username not found.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Github API | SAM OCHU</title>
    <link rel="stylesheet" href="githubApi.css">
</head>
<body>
    <main>
        <div class="form">
            <div class="container">
                <form action="GithubApi.php" method="post">
                    <div class="input-box">
                        <input type="text" name="username" id="username" placeholder="Enter github username." required>
                        <button type="submit" class="sach-btn" id="btn">Search</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="user-details">
            <div class="container">
                <div class="content">
                    <div class="sam" id="sho">Result</div>
                    <div class="user-name"><?php if (isset($jina)) echo htmlspecialchars($jina); ?></div>
                    <div class="follow">
                        <div class="following"><?php if (isset($Following)) echo htmlspecialchars($Following); ?></div>
                        <div class="followers"><?php if (isset($Followers)) echo htmlspecialchars($Followers); ?></div>
                    </div>
                    <div class="repo">
                        <?php if (isset($repo)) echo htmlspecialchars($repo); ?>
                    </div>
                    <div class="star">
                        <?php if (isset($star)) echo htmlspecialchars($star); ?>
                    </div>
                    <div class="error">
                        <?php if (isset($error)) echo htmlspecialchars($error); ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        const show = document.getElementById("sho");
        const btn = document.getElementById("btn");
        btn.onclick = () => {
            show.style.display="none"
        }
    </script>
</body>
</html>
