# pcext_transinfos
transinfos extend for phpcms <br/>
phpcms v9 新加一个module等 <br/>
1、写一个类（libs目录-单例）调用和初始化常用类（包括model），扩展了model类，添加了批量creat，update方法等。 <br/>
2、添加一模块主要实现推送功能。通过此模块可以批量推送本网站不同模型数据（或表单数据）到相应子站栏目下。基本原理就是读取本站数据表数据，通过curl推送到子站，其中有接收数据密码验证（未做时效判断），推送后数据更新（通过replace into，可考虑update),同步删除等。 <br/>
