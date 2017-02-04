#Date: 2016.09.09
#Author: Ryan Qu
#Version: 1.0
#Description: Insert words into mysql.
#Encoding: UTF-8

import MySQLdb
import time
import string
import sys
import os
import os.path

#Set Default Encoding to utf-8. Escaping from the encoding error of python shell.
reload(sys)
sys.setdefaultencoding('utf-8')

#charset='utf-8'
hostname='hostname'
username='username'
password='password'
database='letterpress'
table='words'
filepath = "filepath"

head_list = list()
words_list = list()
def dirFile(filepath):
    pathDir =  os.listdir(filepath)
    #count=0
    for allDir in pathDir:
        if os.path.splitext(allDir)[1] == '.txt':
            child = os.path.join('%s%s' % (filepath, allDir))
            #print child.decode('gbk')
            f = open('%s'%allDir,'r')
            for line in open('%s'%allDir):
                line = f.readline()
                line = line.strip()
                head=line[0:2]
                #count=count+1
                #print line
                head_list.append(head)
                words_list.append(line)
            f.close()
    #print count
dirFile(filepath)
id_list=range(1,1+len(words_list))
words=zip(id_list,head_list,words_list)
#print words[6316:6319]

# f=open('allwords.txt','w')
# print >> f, words 
# f.close()

try:
    conn=MySQLdb.Connect(host='%s'%hostname,user='%s'%username,passwd='%s'%password,port=3306)
    cur=conn.cursor()
    print 'Connected to MySQL Success!'
    #cur.execute('CREATE DATABASE IF NOT EXISTS %s' %database)
    conn.select_db('%s' %database)
    cur.execute(
        """CREATE TABLE IF NOT EXISTS `%s` (
            `words_Id` int(6) NOT NULL,
            `words_Head` char(2)  NOT NULL,
            `words_Full` text NOT NULL,
            PRIMARY KEY (`words_Id`),
            UNIQUE KEY `words_Id` (`words_Id`)
        )"""%table
    )
    #sql="INSERT INTO %s values(%s, %s, %s)"%table
    #print sql
    #tmp=[(1,'aa','aa'),(2,'aa','aaf')]
    cur.executemany("INSERT INTO words values(%s, %s, %s)", words)
    conn.commit()
    cur.close()
    conn.close()
    print 'Initialize MySQL Success!'
except MySQLdb.Error,e:
    print "Mysql Error %d: %s" % (e.args[0], e.args[1])