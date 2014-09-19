#!/usr/bin/python2.4

import math
import random
import json
from learning import LearningMechanism
import urllib2
from types import NoneType
import re

with open ("demo_list.txt", "r") as myfile:
    tweets=myfile.read().replace('\n', '')

last_sentiment = 0
slast_sentiment = 0

counter = 0

positive_sentiment = None
weighted_sentiment = None

tweet_list = []

tweets = json.loads(tweets)
sentiments = re.findall(r'\'Sentiment_score\': u\'([^<>]*?)\'', str(tweets), flags=re.S|re.I|re.M)
for sentiment in sentiments:
    if sentiment < 0:
        counter =- 1
    else:
        counter =+ 1
    slast_sentiment = last_sentiment
    last_sentiment = sentiment
    
if counter > 0:
    positive_sentiment = True
else:
    positive_sentiment = False
    
probability = random.getrandbits(5)
if positive_sentiment:
    if probability <= 3:
        weighted_sentiment = "Negative"
    else:
        weighted_sentiment = "Positive"
elif not positive_sentiment:
    if probability <= 3:
        weighted_sentiment = "Positive"
    else:
        weighted_sentiment = "Negative"

lc = LearningMechanism()
lc.total_counter = 0
predicted = lc.RecieveValue(last_sentiment, slast_sentiment, weighted_sentiment)
post = json.dumps(predicted)
print "json:%s" % json.loads(post)
urllib2.urlopen("", data=post)