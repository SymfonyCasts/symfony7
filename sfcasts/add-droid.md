# Add Droid

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

# Setup

Coming soon...


Okay, let's tackle the last part of many to many. We have our `Starship` entity, which is many to many over to our `Droid` entity. We saw that in the migration, this creates a join table, which is how we're gonna manage which droids are related to which ships. The question now is how do we actually assign a droid to a ship? And once again, we're gonna do this inside our `AppFixtures` so we can see exactly how to do it manually.

To start up here, it doesn't really matter where, I'm gonna paste in some code that adds three droids to our system. I'll hit option enter right here and the import `Droid` class at that use statement. So nothing fancy here. Create a new droid, setting the required properties, persisting and then flushing down here.

The question now is how do we assign this droid to this starship? First, I'll set that starship to a variable. So we'll say `Starship = StarshipFactory::createOne([]);`. The answer to how we relate these two things is delightfully simple. And it's gonna remind you exactly of our one to many relationship. I bet you can even guess. So down here, anywhere before the flush. So anywhere up here. We're gonna say `$starship->addDroid($droid1);`. Just that simple. Down here, we'll do the same thing. `$starship->addDroid($droid2);`. And finally down here right before the flush, so it saves. `$starship->addDroid($droid3);`. And that is it.

The question now is how do we assign the droid to the ship? Because the crew is getting hungry for pancakes. All right, let's try the fixtures. 

```terminal
symfony console doctrine:fixtures:load
```

Cool, no errors. Let's see what actually happened in the database. 

```terminal
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

Because remember, we created three droids. So we see three rows inside of that table. Nothing fancy there. Now let's look at the join table. It's called `starship_droid`. And check that out, three there, because each of our three droids is assigned to this starship. So once again, the awesome thing is that in doctrine, all we need to think about is relating objects, relating this droid to this starship. Doctrine entirely handles inserting and deleting rows into the join table.

Okay, so check this out. At this point here, once we call this flush, we're gonna have three rows in that join table for our three droids. So let's try something after the flush. So after we have those three rows in the join table, let's call `$starship->removeDroid($droid1);`.

```terminal
symfony console doctrine:fixtures:load
```

And let's check out our join table. And sweet, you can see there are two rows in there. So if we could have froze right here, what we would have seen is three rows, and then a second later, it actually deleted one of the rows if there's only two at the end. So once again, doctrine is handling all of that for us, which is absolutely magical.

Now, one last thing I wanna touch on here with many to many is earlier, we talked about owning versus inverse sides of a relationship. And this mostly doesn't matter because as we can see here, our methods here actually synchronize the other side of the relationship. So it actually adds the, when you call `addDroid()`, it actually adds it to the other side. So mostly owning versus inverse side doesn't matter. Now, in a many to many, either side of the relationship can be the owning side. The way you figure it out is by this `inverseBy`. So notice it says `ManyToMany` and `inverseBy` starships. So it's actually pointing over at the `Droid` `starships` property and saying that is the owning side, that's the map side. It's actually saying that's the inverse side. So that means this is the inverse side of the relationship and `starship_droids` is the map side. Now this, again, this mostly doesn't matter because you can set either side. The only reason I bring it up is that if you want to control what the join table's name is, you can add annotation here called `joinTable`, but it has to go on the owning side. So it has to go on this, it has to go right here, basically right on this line. Other than that, forget I said anything because it's not a big deal.
