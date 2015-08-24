开发说明
====
+ 框架使用的是yaf
++ 规范controllers文件名首字母大写其他小写对于的类名“目录_文件名Controller”,首字母大写
++ 规范model文件名首字母大写其他随便但是与类名一直。类名“目录_文件名Model”
+ 每一个媒体的对接分别在controllers下面的Media目录下面对于的model在models下面的Media下面
+ Controllers下面的User主要用于回调接口获取用户信息，对于的model是User
+ 登陆后的各媒体的用户分别存在媒体的自己的表中然后汇总`third_login`.`media_user`，mediaUserID的规则是media_id*10000000000+user_id