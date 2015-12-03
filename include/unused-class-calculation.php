<?php


function itemCorrect($crrItem, $pid) {

}


function damageCalculate($self, $foe, $condition) {

    //本函数计算技能造成的威力
    //需要一个变身或模仿时的临时数据储存栏
    //调用函数时需要将数据封装为一个数组
    //本方（self）：level,bsStr,ivStr,evStr,item_id,nature,ability,gender,
    //对方（foe）：
    //场地（condition）：weather,

    /**
     *    伤害计算流程
     *    技能威力
     *        -计算技能基础威力。如果技能基础威力为1，通过技能效果计算出实际威力。
     *        -如果攻击方是技师特性，且技能威力≤60，技能威力×1.5。
     *        -如果攻击方是舍身、铁拳特性，使用符合要求的技能时，技能威力×1.2。
     *        -[特性/条件]如果攻击方是深绿、猛火、激流、虫之预感特性，HP低于1/3时，相应属性技能威力×1.5。
     *        -[特性/条件]如果攻击方是斗争心特性，场上存在同性怪兽时，技能威力×1.25。
     *        -[特性/条件]如果攻击方是斗争心特性，场上存在异性怪兽时，技能威力×0.75。
     *        -如果攻击方处于一个帮手状态，技能威力×1.5；如果攻击方处于两个帮手状态，技能威力×2.25。
     *        -如果攻击方处于充电状态，攻击方电属性技能威力×2。
     *        -如果场上存在玩水状态的怪兽，攻击方火属性技能威力×0.5。
     *        -如果场上存在玩泥状态的怪兽，攻击方电属性技能威力×0.5。
     *        -如果攻击方携带属性强化道具或石板，并与技能属性相同，技能威力×1.2。
     *            -如果攻击方携带力量头巾，并且使用物理技能，技能威力×1.1。
     *            -如果攻击方携带知识眼镜，并且使用特殊技能，技能威力×1.1。
     *        -如果攻击方是携带电珠的皮卡丘，技能威力×2。
     *        -如果攻击方是携带金刚玉的帝牙卢卡，攻击方钢或龙属性技能威力×1.2。
     *        -如果攻击方是携带白玉的帕路奇犽，攻击方水或龙属性技能威力×1.2。
     *        -如果攻击方是携带白金玉的骑拉帝纳，攻击方鬼或龙属性技能威力×1.2。
     *        -如果防御方是耐热特性，攻击方火属性技能威力×0.5。
     *        -如果防御方是干燥肌肤特性，攻击方火属性技能威力×1.25。
     *        -如果防御方是厚脂肪特性，攻击方火或冰属性技能威力×0.5。
     *    攻击力
     *        -如果使用物理技能，攻击力=攻击方攻击能力值×攻击等级修正。
     *            -[特性]如果攻击方持有大力士或瑜伽之力特性，攻击力×2。
     *            -[特性]如果攻击方持有紧张特性，攻击力×1.5。
     *            -[特性/条件]如果攻击方持有根性特性，并且处于异常状态，攻击力×1.5。
     *            -[特性/条件]如果天气为晴天，并且攻击方场上存在花之礼物特性的怪兽，攻击力×1.5。
     *            -[特性/条件]如果攻击方处于缓慢启动状态，攻击力×0.5。
     *            -[状态/特性]如果攻击方处于烧伤状态，并且不持有根性特性时，攻击力×0.5。
     *            -[道具]如果攻击方携带专爱头巾，攻击力×1.5。
     *            -[专用道具]如果攻击方为携带粗骨头的可拉可拉或嘎拉嘎拉，攻击力×2。
     *        -如果使用特殊技能，攻击力=攻击方特攻能力值×特攻等级修正。
     *            -[特性/条件]如果攻击方持有正极特性，并且攻击方场上存在正极或负极特性的怪兽时，攻击力×1.5。
     *            -[特性/条件]如果攻击方持有负极特性，并且攻击方场上存在正极或负极特性的怪兽时，攻击力×1.5。
     *            -[特性/条件]如果天气为晴天，并且攻击方持有太阳力量特性时，攻击力×1.5。
     *            -[道具]如果攻击方携带专爱眼镜，攻击力×1.5。
     *            -[专用道具]如果攻击方为携带心之水珠的拉帝亚斯或拉帝欧斯，攻击力×1.5。
     *            -[专用道具]如果攻击方为携带深海之牙的珍珠贝，攻击力×1.5。
     *    防御力
     *        -如果使用物理技能，防御力=防御方防御能力值×防御等级修正。
     *            -[特性/条件]如果防御方是神秘鳞片特性，并且处于异常状态，防御力×1.5。
     *            -[专用道具/条件]如果防御方是携带金属粉末的百变怪，并且不处于变身状态，防御力×2。
     *        -如果使用特殊技能，防御力=防御方特防能力值×特防等级修正。
     *            -[场地/条件]如果天气是沙暴，并且防御方是岩属性时，防御力×1.5。
     *            -[特性/条件]如果天气是晴天，并且防御方场上存在花之礼物特性的怪兽，防御力×1.5。
     *            -[专用道具]如果防御方是携带心之水珠的拉帝亚斯或拉帝欧斯，防御力×1.5。
     *            -[专用道具]如果防御方是携带深海之鳞的珍珠贝，防御力×1.5。
     *    伤害计算公式
     *        伤害 = （攻击力 × （攻击方等级 × 2 ÷ 5 + 2） × 技能威力 ÷ 防御力 ÷ 50 × 修正1 + 2）× 修正2
     *    修正1
     *        -如果技能目标范围内有多于一只怪兽，伤害×3/4。
     *        -如果攻击方处于引火状态，并且技能是火属性，伤害×1.5。
     *        -如果场上天气是晴天：
     *            -攻击方技能是火属性时，伤害×1.5。
     *            -攻击方技能是水属性时，伤害×0.5。
     *        -如果场上天气是雨天：
     *            -攻击方技能是水属性时，伤害×1.5。
     *            -攻击方技能是火属性时，伤害×0.5。
     *            -攻击方技能是太阳光线时，伤害×0.5。
     *        -如果防御方场上存在反射盾，攻击方使用物理技能，并且未出现会心一击，伤害×0.5（双打对战或三打对战时为2/3）。
     *        -如果防御方场上存在光之壁，攻击方使用特殊技能，并且未出现会心一击，伤害×0.5（双打对战或三打对战时为2/3）。
     *    修正2
     *        -出现会心一击时：如果攻击方是狙击手特性，伤害×3，否则伤害×2。
     *        -如果攻击方携带生命之玉，伤害×1.3。
     *        -如果攻击方携带节拍器，伤害×（1.0～2.0）。
     *        -如果攻击方处于先取状态，伤害×1.5。
     *        -从[85,100]中产生随机数R，伤害×R/100。
     *        -如果攻击方属性与技能属性相同，伤害×1.5。
     *        -属性相克修正。
     *        -如果攻击方是有色眼镜特性，防御方对技能有抗性时，伤害×2。
     *        -如果攻击方是分析特性，且在全场最后行动，伤害×1.3。
     *        -如果攻击方携带达人腰带，技能属性克制时，伤害×1.2。
     *        -如果防御方是过滤器、坚硬岩石特性，受到属性相克攻击时，伤害×0.75。
     *        -如果防御方是多重鳞片特性，且满HP时，伤害×0.5。
     *        -如果防御方属性抗性果实发动，伤害×0.5。
     */

    //$result['damage'] = floor(


    //获取道具加成
    switch($iid) {

    }

    //获取
    switch($abi) {

    }

}

?>