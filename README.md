# miniproLinkInfo
通过小程序链接（#小程序://）提取小程序详细信息

**例如：#小程序://企查查/WLgi1iIchDtLwQu**

通过这种只能在微信内打开的小程序专用链接，提取小程序`Appid\path\query`等信息

# 技术原理

其实就是微信公众号插入小程序的搜索框，粘贴，抓个包的事。

![image](https://p2.ssl.qhimg.com/t11b673bcd60f5a8f1e880bb9a8.png)

# 成品

其中`api.php`需要修改里面的`token\fingerprint\cookie`
至于如何修改，这些值怎么获得，我就不多说，看得懂的人才配使用。

`imageProxy.php`是图片代理，因为小程序头像图片地址防盗链，无法正常渲染，需使用代理绕过防盗链。

![image](https://p4.ssl.qhimg.com/t11b673bcd60343868023413a73.png)

# 须知
1. 使用这个接口导致你的公众号被封，你自己承担；
2. cookie、token、fingerprint等参数会过期，自己想办法持久化更新；

# 作者
liKeYun
