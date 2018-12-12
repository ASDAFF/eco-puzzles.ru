<div id="appBasket" data-load="<?=SITE_TEMPLATE_PATH?>/images/picLoad.gif">
    <div id="appBasketContainer">
        <div class="heading">Товар добавлен в корзину <a href="#" class="close closeWindow"></a></div>
        <div class="container">
            <div class="picture">
                <a href="#" class="moreLink"><img src="" alt="" class="image"></a>
            </div>
            <div class="information">
                <div class="wrapper">
                    <a href="#" class="name moreLink"></a>
                    <a class="price"></a>
                    <div class="qtyBlock">
                        <label class="label">Кол-во: </label><a href="#" class="minus"></a><input type="text" class="qty" value=""><a href="#" class="plus"></a>
                    </div>
                    <div class="sum">
                        Итого: <span class="allSum"><s class="discount"></s></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="lower">
            <table class="tools">
                <tr>
                    <td class="icon"><a href="#" class="addCompare" data-no-label="Y"><span class="ico"></span></a></td>
                    <td class="icon"><a href="#" class="addWishlist" data-no-label="Y"><span class="ico"></span></a></td>
                    <td class="icon"><a class="availability"><span class="ico"></span></a></td>
                    <td class="icon"><a href="#" class="delete"><span class="ico"></span></a></td>
                    <td class="continue"><a href="#" class="closeWindow"><img src="<?=SITE_TEMPLATE_PATH?>/images/continue.png" alt=""><span class="text">Продолжить покупки</span></a></td>
                    <td class="goToBasket"><a href="<?=SITE_DIR?>personal/cart/"><img src="<?=SITE_TEMPLATE_PATH?>/images/goToBasket.png" alt=""><span class="text">Перейти в корзину</span></a></td>
                </tr>
            </table>
        </div>
    </div>
</div>