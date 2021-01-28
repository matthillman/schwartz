import { GuildChannel, Message, MessageAttachment, TextChannel } from 'discord.js';
import Enmap from 'enmap';
import { inject, injectable } from 'inversify';
import { loadImage, createCanvas } from 'canvas';
import { TYPES } from '../ioc/types';
import { BaseCommand, CommandCategory, HelpText  } from './command';

@injectable()
export class Image extends BaseCommand {
    name = 'image';
    aliases: string[] = [];
    help: HelpText = {
        category: CommandCategory.util,
        description: `Image combining functions`,
        usage: `image register [input|output] [channel]
\t**NOTE**: Must at least set #output before using generate
image generate [optional # of images to read from #input] [base image URL] [optional second image]
\t**NOTE**:Appends a given image to a set of images in #input and posts to #output.
\tIf two images are given then they are used instead of reading from #input.`,
    };

    @inject(TYPES.ImageDB) private imageDB: Enmap;

    async execute([command, ...args]: string[], message: Message): Promise<boolean> {
        if (command === 'generate') {
            await this.generate(args, message);
        } else if (command === 'register') {
            await this.register(args, message);
        }
        return true;
    }

    async register([command, ...args]: string[], message: Message) {
        const setChannel = async (label, chanArg) => {
            this.imageDB.ensure(message.guild.id, {});

            const channelName = chanArg.replace(/^#/g, '');
            let channel: GuildChannel;

            if (channelName.startsWith('<#')) {
                channel = message.guild.channels.resolve(channelName.replace(/^<#/, '').replace(/>$/, ''));
            } else {
                channel = message.guild.channels.cache.find(c => c.name === channelName);
            }

            if (!channel) {
                await message.reply(`Can't find a channel named "${name}"`);
                return;
            }

            this.imageDB.set(message.guild.id, channel.id, label);
        };

        if (command === 'input' || command === 'output') {
            const chan = args.shift();
            setChannel(command, chan);
            message.reply(`**${chan}** registered for image **${command}**`);
        } else {
            message.reply(`Please specify either "input" or "output" and a channel`);
        }
    }

    async generate([baseImage, rightImage]: string[], message: Message) {
        const channelConfig = this.imageDB.get(message.guild.id);
        if (!channelConfig) {
            await message.reply(`You need to set up input/output channels before running generate`);
            return;
        }

        const inputChannel = message.guild.channels.resolve(channelConfig.input) as TextChannel;
        const outputChannel = message.guild.channels.resolve(channelConfig.output) as TextChannel;

        if (!rightImage && !inputChannel) {
            await message.reply(`Can't find input channel "${channelConfig.input}, please make sure you've set it up with *image register input*"`);
            return;
        }
        if (!outputChannel) {
            await message.reply(`Can't find output channel "${channelConfig.output}", please make sure you've set it up with *image register output*`);
            return;
        }

        let inputURLs = [];
        if (rightImage && this.validURL(baseImage)) {
            inputURLs = [baseImage];
        } else {
            const inputMessages = await inputChannel.messages.fetch({ limit: (this.validURL(baseImage) ? 50 : +baseImage) });
            inputURLs = inputMessages.reduce((acc, m) => acc.concat(this.validURL(m.content) ? [m.content] : m.attachments.map(a => a.url)), []);
        }
        const baseIMG = await loadImage(rightImage || baseImage);

        for (const flairURL of inputURLs) {
            const flairIMG = await loadImage(flairURL);
            let scaledWidth = 0;
            let scaledHeight = 0;
            let x = 0;
            let y = 0;
            let totalHeight = 0;
            let totalWidth = 0;

            if (flairIMG.height > flairIMG.width) {
                scaledWidth = flairIMG.width * baseIMG.height / flairIMG.height;
                scaledHeight = baseIMG.height;
                x = scaledWidth;
                y = 0;
                totalWidth = scaledWidth + baseIMG.width;
                totalHeight = baseIMG.height;
            } else {
                scaledHeight = flairIMG.height * baseIMG.width / flairIMG.width;
                scaledWidth = baseIMG.width;
                x = 0;
                y = baseIMG.height;
                totalWidth = baseIMG.width;
                totalHeight = baseIMG.height + scaledHeight;
            }

            const canvas = createCanvas(totalWidth, totalHeight);
            const ctx = canvas.getContext('2d');

            if (flairIMG.height > flairIMG.width) {
                ctx.drawImage(flairIMG, 0, 0, scaledWidth, scaledHeight);
                ctx.drawImage(baseIMG, x, y, baseIMG.width, baseIMG.height);
            } else {
                ctx.drawImage(baseIMG, 0, 0, baseIMG.width, baseIMG.height);
                ctx.drawImage(flairIMG, x, y, scaledWidth, scaledHeight);
            }

            const attachment = new MessageAttachment(canvas.toBuffer(), 'generated.png');

            await outputChannel.send('', attachment);
        }

        message.reply(`${inputURLs.length} images generated`);
    }

    private validURL(str) {
        const pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
            '(\\#[-a-z\\d_]*)?$','i' // fragment locator
        );
        return !!pattern.test(str);
    }
}
