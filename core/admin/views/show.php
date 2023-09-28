<div class="vg-wrap vg-element vg-ninteen-of-twenty">
    <?php
    ?>
    <div class="vg-element vg-fourth">
        <a href="<?= $this->adminPath ?>add/<?= $this->table?>"
           class="vg-wrap vg-element vg-full vg-firm-background-color3 vg-box-shadow">
            <div class="vg-element vg-half vg-center">
                <img src="<?= PATH . ADMIN_TEMPLATE ?>img/plus.png" alt="plus">
            </div>
            <div class="vg-element vg-half vg-center vg-firm-background-color1">
                <span class="vg-text vg-firm-color3">Add</span>
            </div>
        </a>
    </div>
    <?php if(!empty($this->data)) :?>
        <?php foreach($this->data as $item):?>
    <div class="vg-element vg-fourth">
        <a href="<?= $this->adminPath ?>edit/<?= $this->table ?>/<?= $item['id'] ?>"
           class="vg-wrap vg-element vg-full vg-firm-background-color4 vg-box-shadow show_element">
            <div class="vg-element vg-half vg-center">
                <?php if(!empty($data['img'])) : ?>
                <img src="<?= PATH . UPLOAD_DIR . $data['img']?>" alt="image">
                      <?php endif; ?>
            </div>
            <div class="vg-element vg-half vg-center">
                <span class="vg-text vg-firm-color1"><?= $item['name'] ?></span>
            </div>
        </a>
    </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

