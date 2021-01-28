
const Enmap = require("enmap");
const EnmapLevel = require("enmap-level");
const SQLite = require("enmap-sqlite");

const settingsSource = new EnmapLevel({ name: "settings", dataDir: '../bot/data' });
const imageChannelsSource = new EnmapLevel({ name: "image-generation", dataDir: '../bot/data' });
const recruitingChannelsSource = new EnmapLevel({ name: "recruiting-watcher", dataDir: '../bot/data' });
const payoutsSource = new EnmapLevel({name: "payouts", dataDir: '../bot/data' });
const schedulesSource = new EnmapLevel({name: "schedules", dataDir: '../bot/data' });
const handbooksSource = new EnmapLevel({name: "handbooks", dataDir: '../bot/data' });

const settingsTarget = new SQLite({ name: "settings", dataDir: '../bot-2/data' });
const imageChannelsTarget = new SQLite({ name: "image-generation", dataDir: '../bot-2/data' });
const recruitingChannelsTarget = new SQLite({ name: "recruiting-watcher", dataDir: '../bot-2/data' });
const payoutsTarget = new SQLite({name: "payouts", dataDir: '../bot-2/data' });
const schedulesTarget = new SQLite({name: "schedules", dataDir: '../bot-2/data' });
const handbooksTarget = new SQLite({name: "handbooks", dataDir: '../bot-2/data' });

const migrate = async () => {
    await Enmap.migrate(settingsSource, settingsTarget);
    await Enmap.migrate(imageChannelsSource, imageChannelsTarget);
    await Enmap.migrate(recruitingChannelsSource, recruitingChannelsTarget);
    await Enmap.migrate(payoutsSource, payoutsTarget);
    await Enmap.migrate(schedulesSource, schedulesTarget);
    await Enmap.migrate(handbooksSource, handbooksTarget);
    return;
};

migrate().then(() => process.exit(0));
