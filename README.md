# IP/Whois Lookup System
This is a Domain/IP Whois Lookup System.
you can see it at website: https://whois.cz9.cn
It's well work at PHP5.3(or newer) + mysql + apache
nginx config like this:
location / {
    try_files $uri $uri/ /index.php/$args;
}
ymqz.sql.zip is a SQL database.

这是一个开源的IP/域名WHOIS查询系统，
你可以在网站：https://whois.cz9.cn 查看演示。
本程序良好运行于：PHP5.3（或更新） + MYSQL + APACHE 工作环境。
当然 nginx 也是可以的，配置如下：
location / {
    try_files $uri $uri/ /index.php/$args;
}
ymqz.sql.zip是一个SQL数据库文件，请自行导入你的数据库。
