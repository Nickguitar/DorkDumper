# DorkDumper
Simple PHP script that dumps websites vulnerable to SQL injection (GET)

Usage:
lol@lol:/:~$ php ddumper.php dork pages file.txt
e.g.
lol@lol:/:~$ php ddumper.php page.php?id= 20 URL.txt

The script will generate the file URL.txt containing all the vulnerable URL's that contains 'page.php?id=' found in 20 of Bing search.
