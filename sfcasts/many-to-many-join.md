# Many To Many Join

Coming soon...


Welcome back, my friends! In our last tutorial about Doctrine, we accomplished quite a
bit. We created an entity, migrations, fixtures, and more. But, you can't really build
anything impressive without understanding database relationships. For example, that slice
of pizza belongs to me, or I have many slices of pizza. 

To follow along with what I'm doing, download the course code from this page. After 
unzipping the file, you should see a start directory containing the same code that's 
displayed here. Follow the instructions in the readme.md file for setup. 

The last step is to open a terminal, navigate to your project, and run `symfony serve`. 
Sometimes, you might run this with a "-d" to run it in the background as a daemon. This 
time, I'm running it in the foreground. 

One of the cool features is that you'll see all the server logs appear here. These include 
important ones like the logs showing we're using Tailwind, as well as it downloading and
building in the background. When that's done, scroll up and click this link to open up 
Starshop, our application. 

Starshop is all about repairing ships. All these Starships, the only entity we have 
currently in Source Entity, are coming from the database. But that's kind of boring. So, 
in this tutorial, we're going to start tracking the parts of a ship. As we repair a ship, 
we'll keep track of which parts we've ordered for the ship and their cost. 

By the end, we'll have established some pretty serious relationships that will empower you 
to build whatever you can dream of. Let's get started with that next.
