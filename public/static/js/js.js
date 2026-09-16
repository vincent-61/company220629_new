jQuery(".nav").slide({ type:"menu",  titCell:".m", targetCell:"dl", effect:"slideDown", delayTime:300, triggerTime:100,returnDefault:true  });
jQuery(".banner").slide({mainCell:".bd ul",autoPlay:true});
jQuery(".index-case .content").slide({ mainCell:"ul", effect:"leftLoop", vis:4, scroll:1,  autoPage:true,autoPlay:true});
jQuery(".index-news .content").slide({titCell:"h3", targetCell:"dl",defaultIndex:0,effect:"slideDown",delayTime:300,returnDefault:true});

$(".category h3.on").next(".category ul").slideToggle(500);
  $(".category h3").click(function(){
      $(this).toggleClass("on").siblings('.category h3').removeClass("on");//切换图标
      $(this).next(".category ul").slideToggle(500).siblings(".category ul").slideUp(500);
  });
      
$(function () {
	
	var time;
    var $kefu = $('.kefu');
    var $c = $kefu.find('#kefu');
    $kefu.css({
        'marginTop': -($kefu.height() / 2)
    });
    $c.find('li').on({
        'mouseenter': function () {
            var scope = this;
            time = setTimeout(function () {
                var divDom = $(scope).children('div');
                var maxWidth = divDom.width();
                $(scope).stop().animate({
                    left: 77 - maxWidth
                }, 'normal', function () {
                    var pic = $(scope).find('.kefu-weixin-pic');
                    if (pic.length > 0) {
                        pic.show();
                    }
                });
            }, 100);
        },
        'mouseleave': function () {
            var pic = $(this).find('.kefu-weixin-pic');
            if (pic.length > 0) {
                pic.hide();
            }
            clearTimeout(time);
            $(this).stop().animate({
                left: 0
            }, 'normal', function () {
            });
        }
    });
    $(window).scroll(function () {
        var scrollTop = document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop;
        var eltop = $c.find('.kefu-ftop');
        if (scrollTop > 0) {
            eltop.show();
        } else {
            eltop.hide();
        }
    });
    $c.find('.kefu-ftop').click(function () {
        var scrollTop = document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop;
        if (scrollTop > 0) {
            $('html,body').animate({
                scrollTop: 0
            }, 'slow');
        }
    });
    
})