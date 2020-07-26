<div class="how-it-work container-full">
    <div class="container">
        <h2 class="text-center">
            <span class="clr-red">How</span>
            <span class="clr-white">It works</span>
        </h2>
        <div class="how-it-work-block">
            <?php
            $work_steps = get_field('work_steps');
            foreach ($work_steps as $step) {
                if ($step['work_title'] == 'Chat with A Nerd') {
                    $parent_class = 'chat-block';
                } else {
                    $parent_class = 'services-block';
                }
            ?>
                <div class="<?php echo $parent_class ?>">
                    <img src="<?php echo $step['work_icon'] ?>" />
                    <div class="number"><?php echo $step['work_number']; ?></div>
                    <div class="block-text"><?php echo $step['work_title'] ?></div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>