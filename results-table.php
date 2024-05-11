<div id="results-table">
    <table class="full-width-table">
        <thead>
            <tr>
                <th colspan="1"> Place </th>
                <th colspan="1"> Address </th>
                <th colspan="1"> PottyPin </th>
                <th colspan="1"> Features </th>
                <!-- <th>Status</th>
                < ?php if (isset($_SESSION['islogged']) && $_SESSION['islogged']): ?>
                    <th>Info</th>
                < ?php endif; ?> -->
            </tr>
        </thead>
        
        <tbody>
            <?php 
            $isOdd = true;
            foreach ($combinedResults as $index => $place): 
                $rowColor = $isOdd ? 'odd-color' : 'even-color';
                $isOdd = !$isOdd;

                // Calculate the average score and number of reviews for the current place
                $stmt = $con->prepare("SELECT AVG(score), COUNT(*) FROM reviews WHERE place_id = ?");
                $stmt->bind_param("s", $place['place_id']);
                $stmt->execute();
                $stmt->bind_result($average_score, $num_reviews);
                $stmt->fetch();
                $stmt->close();

                // Calculate the number of full, half, and empty stars
                $fullStars = floor($average_score);
                $halfStar = ($average_score - $fullStars >= 0.5) ? 1 : 0;
                $emptyStars = 5 - $fullStars - $halfStar;
            ?>
            <tr class="collapsible-row <?= $rowColor ?>" data-group="<?= $index ?>">
                <td><b><?= $place['name'] ?></b></td>
                <td><a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($place['vicinity']) ?>" target="_blank"> Directions </a></td>
                <td class="text-center"><?= $place['bathroom_code'] ? $place['bathroom_code'] : 'No Data' ?></td>
                <td class="text-center">
                    <?= !empty($place['wheelchair']) ? '<img src="img/wheelchair.png" alt="Wheelchair Accessible" class="small-icon">' : '' ?>
                    <?= !empty($place['has_gender_neutral']) ? '<img src="img/gender-neutral.png" alt="Gender Neutral" class="small-icon">' : '' ?>
                    <?= !empty($place['has_family_room']) ? '<img src="img/family.png" alt="Family Room" class="small-icon">' : '' ?>
                    <?= !empty($place['needs_key']) ? '<img src="img/key.png" alt="Needs Key" class="small-icon">' : '' ?>
                    <?= !empty($place['needs_coin']) ? '<img src="img/coin.png" alt="Needs Coin" class="small-icon">' : '' ?>
                </td>
            </tr>
            <tr class="collapsible-content <?= $rowColor ?>" data-group="<?= $index ?>" style="display: none;">
                <td class="star-rating text-center">
                    <?php
                    // Display full stars
                    for ($i = 0; $i < $fullStars; $i++) {
                        echo '<svg class="star" viewBox="0 0 24 24" fill="gold" width="24" height="24"><path d="M12 .288l2.833 8.718h9.167l-7.417 5.399 2.834 8.718-7.417-5.399-7.417 5.399 2.834-8.718-7.417-5.399h9.167z"/></svg>';
                    }
                    // Display half star
                    if ($halfStar) {
                        echo '<svg class="star-half" viewBox="0 0 24 24" fill="gold" width="24" height="24"><defs><linearGradient id="half"><stop offset="50%" stop-color="gold"/><stop offset="50%" stop-color="lightgray"/></linearGradient></defs><path d="M12 .288l2.833 8.718h9.167l-7.417 5.399 2.834 8.718-7.417-5.399-7.417 5.399 2.834-8.718-7.417-5.399h9.167z" fill="url(#half)"/></svg>';
                    }
                    // Display empty stars
                    for ($i = 0; $i < $emptyStars; $i++) {
                        echo '<svg class="star-empty" viewBox="0 0 24 24" fill="lightgray" width="24" height="24"><path d="M12 .288l2.833 8.718h9.167l-7.417 5.399 2.834 8.718-7.417-5.399-7.417 5.399 2.834-8.718-7.417-5.399h9.167z"/></svg>';
                    }
                    ?>
                    <span> <?= round($average_score, 1) ?></span>
                    <span> (<?= $num_reviews ?> reviews)</span>
                    <br>
                    <?php 
                        if (isset($place['opening_hours'])) {
                            echo $place['opening_hours']['open_now'] ? "<span style='color:green'><b> Open </b></span>" : "<span style='color:red'><b> Closed </b></span>";
                        } else {
                            echo "";
                        }
                    ?>
                    <ul style="text-align: left;">
                        <?php 
                        // Fetch reviews and contributors for the current place_id
                        $stmt = $con->prepare("SELECT review, contributor, date FROM reviews WHERE place_id = ?");
                        $stmt->bind_param("s", $place['place_id']);
                        $stmt->execute();
                        $stmt->bind_result($review, $contributor, $date);

                        while ($stmt->fetch()) {
                            echo "<hr><li><i>" . htmlspecialchars($review) . "<i> (<b> by " . htmlspecialchars($contributor) . "</b> ".htmlspecialchars($date).")</li>";
                        }
                        $stmt->close();
                        ?>
                    </ul>

                </td>
                <td><?= $place['formatted_address'] ?></td>
                <td><b> Past PottyPins </b></td>
                <?php if (isset($_SESSION['islogged']) && $_SESSION['islogged']): ?>
                    <td class="text-center">
                        <a href="edit.php?place_id=<?= urlencode($place['place_id']) ?>" class="btn btn-primary"> Edit or Review </a>
                    </td>
                <?php else: ?>
                    <td class="text-center">
                        Must be logged in to Edit / Review
                    </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>


    </table>
</div>