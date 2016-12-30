
define(['character'], function(Character) {

    var NpcTalk = {
        "guard": [
            "嘿，你好。",
            "我们不查身份证。",
            "你不是我要找的人。",
            "一直走，一直走下去···",
            "我不喜欢雾霾天···"
        ],
    
        "king": [
            "嘿, 我是这里的国王。",
            "我在这里奔跑，",
            "像BOSS一样。",
            "我对人民讲话，",
            "像BOSS一样。",
            "我戴着王冠，",
            "像BOSS一样。",
            "我整天无所事事，",
            "像BOSS一样。",
            "现在让我静静，",
            "像BOSS一样。"
        ],
    
        "villagegirl": [
            "嘿，冒险家！",
            "你喜欢这里吗？",
            "是不是很疯狂！",
            "你想做什么就做什么！",
            "不花钱。",
            "什么？你有钱？那捐给CC吧。"
        ],
    
        "villager": [
            // "Howdy stranger. Do you like poetry?",
            "你好啊，新来的！是妹子吗？",
            // "Roses are red, violets are blue...",
            "如你所见，玫瑰是红的，天空是蓝的。",
            // "I like hunting rats, and so do you...",
            "我喜欢杀老鼠，你也一样吧，少年。",
            // "The rats are dead, now what to do?",
            "老鼠已经死了，现在该干点什么呢？",
            // "To be honest, I have no clue.",
            "说实话，我不知道···",
            // "Maybe the forest, could interest you...",
            "也许森林, 可以使你着迷。",
            // "or instead, cook a rat stew."
            "或者，施展你的厨艺，烤一只老鼠如何？"
        ],
    
        "agent": [
            // "Do not try to bend the sword",
            "别白费力气了，剑是掰不弯的。",
            // "That's impossible",
            "那是不可能的！",
            // "Instead, only try to realize the truth...",
            "相反，去尝试了解事情的真相吧。",
            // "There is no sword."
            "并没有大宝剑。"
        ],
    
        "rick": [
            // "We're no strangers to love",
            "我们相爱已久，已不陌生",
            // "You know the rules and so do I",
            "你我都是知道规则的。",
            // "A full commitment's what I'm thinking of",
            "我在想一个完全的承诺。",
            // "You wouldn't get this from any other guy",
            "你不会从其他人那里获得这玩意儿的。",
            // "I just wanna tell you how I'm feeling",
            "我只是想告诉你我的感受。",
            // "Gotta make you understand",
            "要让你明白。",
            // "Never gonna give you up",
            "不要放弃！",
            // "Never gonna let you down",
            "不用倒下！",
            // "Never gonna run around and desert you",
            "不要到处跑，走丢了哦！^_^",
            // "Never gonna make you cry",
            "男子汉要坚强，不要哭哦。",
            // "Never gonna say goodbye",
            "勿言别。",
            // "Never gonna tell a lie and hurt you"
            "不要听信谎言，伤害了自己。"
        ],
        
        "scientist": [
            // "Greetings.",
            "你好啊！^_^",
            // "I am the inventor of these two potions.",
            "我是这两种药剂的发明者哦。",
            // "The red one will replenish your health points...",
            "红色的药剂，会回复你的生命值。",
            // "The orange one will turn you into a firefox and make you invincible...",
            "橙色的会↑↓←→BABA哦 ^_^",
            // "But it only lasts for a short while.",
            "不过无敌状态只会持续一会，不！是一小会，坑爹啊！",
            // "So make good use of it!",
            "所以要在正确的时机使用它。",
            // "Now if you'll excuse me, I need to get back to my experiments..."
            "请原谅我要失陪了，我要回实验室了。"
        ],
    
        "nyan": [
            // "nyan nyan nyan nyan nyan",
            "喵？",
            // "nyan nyan nyan nyan nyan nyan nyan",
            "喵？喵？",
            // "nyan nyan nyan nyan nyan nyan",
            "喵？喵？喵？",
            // "nyan nyan nyan nyan nyan nyan nyan nyan"
            "喵？喵？喵？喵？"
        ],
        
        "beachnpc": [
            // "lorem ipsum dolor sit amet",
            "排版测试",
            // "consectetur adipisicing elit, sed do eiusmod tempor"
            "依然还是测试"
        ],
        
        "forestnpc": [
            // "lorem ipsum dolor sit amet",
            "窈窕淑女，君子好球",
            // "consectetur adipisicing elit, sed do eiusmod tempor"
            "好吧，我污了。"
        ],
        
        "desertnpc": [
            // "lorem ipsum dolor sit amet",
            "窈窕淑女，君子好球",
            // "consectetur adipisicing elit, sed do eiusmod tempor"
            "好吧，我污了。"
        ],
        
        "lavanpc": [
            // "lorem ipsum dolor sit amet",
            "窈窕淑女，君子好球",
            // "consectetur adipisicing elit, sed do eiusmod tempor"
            "好吧，我污了。"
        ],
    
        "priest": [
            // "Oh, hello, young man.",
            "哦，你好啊！年轻人。",
            // "Wisdom is everything, so I'll share a few guidelines with you.",
            "智商压制才是最可怕的，所以，还是让我来告诉你本游戏怎么玩吧！",
            // "You are free to go wherever you like in this world",
            "在本游戏的世界里，你可以想去哪就去哪。",
            // "but beware of the many foes that await you.",
            "但要注意许多敌人,正等待着你。",
            // "You can find many weapons and armors by killing enemies.",
            "杀死小怪可能会掉落武器、铠甲。",
            // "The tougher the enemy, the higher the potential rewards.",
            "越是强大的敌人，击杀后爆的装备就会越好，前提是你的运气好，爆出装备了。",
            // "You can also unlock achievements by exploring and hunting.",
            "你也可以通过探索这个世界和猎杀一些怪物来解锁成就。",
            // "Click on the small cup icon to see a list of all the achievements.",
            "点击小奖杯图标可以看到都有什么成就。",
            // "Please stay a while and enjoy the many surprises of BrowserQuest",
            "请多玩一会，享受本游戏所带给你的一些惊喜吧！",
            // "Farewell, young friend."
            "ヾ(￣▽￣)Bye~Bye~"
        ],
        
        "sorcerer": [
            // "Ah... I had foreseen you would come to see me.",
            "啊哈！我就知道你会来。",
            // "Well? How do you like my new staff?",
            "(⊙v⊙)嗯，你喜欢我的新斗篷吗？",
            // "Pretty cool, eh?",
            "很骚吧？",
            // "Where did I get it, you ask?",
            "你问我从哪里弄来的这玩意？",
            // "I understand. It's easy to get envious.",
            "我知道了，嫉妒很正常。",
            // "I actually crafted it myself, using my mad wizard skills.",
            "实际上，这玩意是我自己做的，用我那炫酷的魔法。",
            // "But let me tell you one thing...",
            "但是，我告诉你一件事：",
            // "There are lots of items in this game.",
            "本游戏有很多物品。",
            // "Some more powerful than others.",
            "有些比其他的更牛逼。",
            // "In order to find them, exploration is key.",
            "为了找到他们，勇于探索才是关键。",
            // "Good luck."
            "祝你好运吧！@_@"
        ],
        
        "octocat": [
            // "Welcome to BrowserQuest!",
            "欢迎来到 BrowserQuest！",
            // "Want to see the source code?",
            "别装了，少年，我知道你想看源码。",
            // 'Check out <a target="_blank" href="http://github.com/mozilla/BrowserQuest">the repository on GitHub</a>'
            '呐~ 就在这里哦 <a target="_blank" href="https://github.com/redoc/zero">the repository on GitHub</a>'
        ],
        
        "coder": [
            // "Hi! Do you know that you can also play BrowserQuest on your tablet or mobile?",
            "你好啊！你知道你还可以在平板电脑或者手机玩BrowserQuest吗?",
            // "That's the beauty of HTML5!",
            "这就是Html5的魅力所在！",
            // "Give it a try..."
            "尝试一下吧！"
        ],
    
        "beachnpc": [
            // "Don't mind me, I'm just here on vacation.",
            "不要管我，我只是在此度假。",
            // "I have to say...",
            "我不得不说......",
            // "These giant crabs are somewhat annoying.",
            "这些巨蟹有点烦人，",
            // "Could you please get rid of them for me?"
            "你能帮我干掉他们吗？"
        ],
        
        "desertnpc": [
            // "One does not simply walk into these mountains...",
            "不要孤身进入这一片的山里。",
            // "An ancient undead lord is said to dwell here.",
            "一个古老的亡灵主据说住在那里。",
            // "Nobody knows exactly what he looks like...",
            "没有人知道他长什么样。",
            // "...for none has lived to tell the tale.",
            "讲故事的都已逝去。",
            // "It's not too late to turn around and go home, kid."
            "回家去吧，还不晚，孩子。"
        ],
    
        "othernpc": [
            // "lorem ipsum",
            "还是那句话：",
            // "lorem ipsum"
            "PHP是世界上最好的语言！"
        ]
    };

    var Npc = Character.extend({
        init: function(id, kind) {
            this._super(id, kind, 1);
            this.itemKind = Types.getKindAsString(this.kind);
            this.talkCount = NpcTalk[this.itemKind].length;
            this.talkIndex = 0;
        },
    
        talk: function() {
            var msg = null;
        
            if(this.talkIndex > this.talkCount) {
                this.talkIndex = 0;
            }
            if(this.talkIndex < this.talkCount) {
                msg = NpcTalk[this.itemKind][this.talkIndex];
            }
            this.talkIndex += 1;
            
            return msg;
        }
    });
    
    return Npc;
});