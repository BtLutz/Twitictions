#!/usr/bin/python2.4

import math
import random
import pdb

class LearningMechanism:
    
    def CalculateSlope(self, a, b):
        return (float(a.get('y')-b.get('y')))/float((a.get('x')-b.get('x')))
    
    def RecieveValue(self, sl, l, ws):
        
        predicted = []
        last = {'x':15, 'y':float(l)}
        slast = {'x':14, 'y':float(sl)}
        print "Sentiment: %s" % ws
        #print last
        #print slast
        
        slope = self.CalculateSlope(slast, last) ##Something seems wrong w/ Next Y..
        print "Slope: %s" % slope                ##Come back later once graph is
        if ws == "Positive":
            temp = math.fabs(slope) + last.get('y')
            positive = True
        else:
            temp = -math.fabs(slope) + last.get('y')
            positive = False
        print "Next Y: %s" % temp
        next_point = {'x': 16, 'y': temp, 'slope':slope}
        predicted.append(next_point)
        
        probability = random.getrandbits(5)
        x = 0
        slast = last
        last = next_point       #AATTTAACCHHHH SLOOOOPPPPEEE
        while x <= 2:
            print next_point, last
            print "Slope: %s" % (slope * 1.03)
            if probability <= 3:
                if not positive:
                    temp = math.fabs(slope) * x
                    positive = True
                else:
                    temp = -math.fabs(slope) * x
                    positive = False
                print "Next Y: %s" % temp
            if probability >= 3 :
                if not positive:
                    temp = -math.fabs(slope) * x
                else:
                    temp = math.fabs(slope) * x
            next_point = {"x":16+x, "y":temp}
            predicted.append(next_point)
            x+=1
        
        return predicted
        
    