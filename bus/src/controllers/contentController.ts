export const updatePageContent = async (req: Request, res: Response) => {
    try {
        const page = await Page.findOneAndUpdate(
            { slug: req.params.slug },
            { content: req.body.content },
            { new: true, upsert: true }
        );
        res.json(page);
    } catch (error) {
        res.status(400).json({ message: 'Error updating page content' });
    }
}; 