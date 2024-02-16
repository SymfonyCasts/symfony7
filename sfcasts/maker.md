# Maker Bundle: Let's Generate Some Code!

Coming Soon...

Congrats on just about making it through the first Symfony tutorial. You've taken a huge step toward building whatever you want out there on the web. To celebrate, I want to play with Maker Bundle, Symfony's awesome tool for code generation. Let's get it installed. composer require symfony-maker-bundle-dev Now I haven't seen that "-"-dev flag yet. It's not that important. If you go over to composer.json and open that up, because of that flag, instead of Maker Bundle going into our require key, it was added down here under the require-dev key. Now by default, when you run composer, it's going to install everything under both require and require-dev, but the purpose of require-dev is supposed to be for packages that you don't need on production, packages that you only need when you're developing locally, and that's because when you do deploy, if you want to, there's a way to tell composer, hey, only install the things under my require key. Don't install the things under my require-dev, that can give you a really small performance boost on production, but mostly it's not that big of a deal, so if you mess up and put Maker Bundle onto the require key, no big deal. Now we just installed a bundle, and I've talked a little bit about the main reason you install a bundle in your application is because bundles give you more services, and services are more tools. In this case, the services that Maker Bundle gave us are services that give us new console commands. Check it out, run bin console, or actually, I'll start running symphony console, which is just an alias for the same thing, and now, thanks to the new bundle, we have a ton of commands that start with make, commands for generating a security system, making a controller, generating doctrine entities to talk to the database, forms, listeners, a registration form, lots and lots of things inside of here. Let's use one of these to actually make our own new custom console command. So run symphony console make command. This will interactively ask us a little bit about our command. Let's call ours app colon ship report. So we just ran make colon command. We're going to call ours app colon ship report, and done. This created exactly one class, source command, ship report command dot php.  Let's go check it out. And awesome. So it's a normal class. This is a service, by the way, if you're wondering, with an attribute above it that describes the name of the command and also description. Inside, there's a configure method where you can add arguments and options, but the main thing is, when somebody calls this command, it's going to call execute, and this IO object here is really cool. It allows us to output things like this note or this success down here. And even though we don't see it, it also allows us to ask the user questions interactively. So just by creating this class, this is already ready for us to use. Watch symphony console app colon ship dash report. And it works. This message down here is coming from the success message at the bottom of our command. And thanks to the configure, these arguments, we have one argument called arg one. We can pass that by saying arguments are just strings we pass after the command. So I'll pass that. And now we get this. You pass an argument, Ryan, which is coming from this spot right here. So awesome. Creating commands is super cool, and there's lots of awesome things you can do with it that we won't go too deeply into right now, but I do want to play with one thing. This IO object can also do a progress bar. So let's pretend like we're building an actual ship report and it requires some heavy queries and we want to show a progress bar on the screen. To do that, we can say IO arrow progress start, and then pass it however many rows of data maybe you're looping through and handling. So let's pretend that we're looping over a hundred rows of data to create this report. Then instead of looping over real data, I'm just going to create a fake loop with a for loop. I'm even going to include the I variable in the middle. And inside here, to advance the progress, we'll say IO arrow progress start. And then here's where we would do our heavy query or heavy work. We're going to fake that by doing a use sleep and pass that 10,000. So just a really short pause there so we can see this working.  And at the end to finish, you can say IO arrow progress finish. That's it. So spin over, give that a try. And oh, that is so cool. We've only scratched the surface on commands, but I'll let you dive in and learn more. That's it. You made it. Give yourself a high five and go grab a soda, coffee, tea, pizza, something special for the occasion. Then try this stuff out, play with it, build a blog, just a couple of static pages, anything. If you have any questions, we watch the comment section below each video closely and answer everyone. Also keep going. In the next tutorial, we're really going to make you dangerous by diving deeper into Symfony's configuration and services, the systems that drive everything you'll do in Symfony. All right, friends, see you next time.