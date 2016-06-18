# Twitch-Channels
Gets a list of channels from a Twitch team, and display streamers who are currently live. Creates cache files to speed up future requests.

The official Twitch API didn't provide a proper endpoint to get all channels from a team, so I went ahead and created a method to do this. First, it checks the total amount of pages from the live_member_list, then gets the channels using DOMXpath (//span[@class="member_name"]). 

# Configuration
On the Application class edit $_teamname = 'esl'; to the corresponding team name.
