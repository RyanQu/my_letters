#Date: 2016.09.09
#Author: Ryan Qu
#Version: 1.0
#Description: Insert words into mysql.
#Encoding: UTF-8

import MySQLdb

def connect_mysql(hostname,username,password,charset):
    try:
        conn=MySQLdb.Connect(host='%s'%hostname,user='%s'%username,passwd='%s'%password,port=3306,charset='%s'%charset)
        cur=conn.cursor()
        print 'Connected to MySQL Success!'
    except MySQLdb.Error,e:
        print "Mysql Error %d: %s" % (e.args[0], e.args[1])
def initialize_mysql(database,table):
    try:
        cur.execute('CREATE DATABASE IF NOT EXISTS %s' %database)
        conn.select_db('%s' %database)
        cur.execute(
            """CREATE TABLE IF NOT EXISTS `%s` (
                `words_Id` int(6) NOT NULL,
                `words_Head` char(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                `words_Full` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`words_Id`),
                UNIQUE KEY `words_Id` (`words_Id`)
            )"""%table
        )
        cur.executemany('INSERT INTO `%s` values(1,aa,aa)')
        conn.commit()
        cur.close()
        conn.close()
        print 'Initialize MySQL Success!'
    except MySQLdb.Error,e:
        print "Mysql Error %d: %s" % (e.args[0], e.args[1])
def update_mysql():
    try:
        cur.execute('TRUNCATE TABLE `stock_list')
        cur.executemany('INSERT INTO `stock_list` values(%s,%s,%s)',stock_list)
        conn.commit()
        cur.close()
        conn.close()
        print 'Update MySQL Success!'
    except MySQLdb.Error,e:
        print "Mysql Error %d: %s" % (e.args[0], e.args[1])